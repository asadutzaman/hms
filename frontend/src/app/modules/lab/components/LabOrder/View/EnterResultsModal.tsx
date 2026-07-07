import React, {FC, useEffect, useState} from 'react'
import {Modal, Input, notification, Spin, Alert} from 'antd'
import {LabTestApi, LabResultApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

interface EnterResultsModalProps {
  visible: boolean
  onClose: () => void
  onSaved: () => void
  orderItem: any
}

const EnterResultsModal: FC<EnterResultsModalProps> = (props) => {
  const {visible, onClose, onSaved, orderItem} = props
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [submitting, setSubmitting] = useState(false)
  const [parameters, setParameters] = useState<any[]>([])
  const [values, setValues] = useState<Record<number, string>>({})

  useEffect(() => {
    if (visible && orderItem?.lab_test_id) {
      setLoading(true)
      LabTestApi.getById(orderItem.lab_test_id)
        .then((res: any) => {
          const params = res?.data?.parameters || res?.data?.data?.parameters || []
          setParameters(params)
          const existing: Record<number, string> = {}
          ;(orderItem.results || []).forEach((r: any) => {
            if (r.lab_test_parameter_id) existing[r.lab_test_parameter_id] = r.result_value
          })
          setValues(existing)
        })
        .catch(() => setParameters([]))
        .finally(() => setLoading(false))
    }
  }, [visible, orderItem])

  const handleOk = async () => {
    const results = parameters
      .filter((p: any) => values[p.id] !== undefined && values[p.id] !== '')
      .map((p: any) => ({lab_test_parameter_id: p.id, result_value: values[p.id]}))

    if (results.length === 0) {
      notification.warning({message: t('Enter at least one result value')})
      return
    }

    setSubmitting(true)
    try {
      await LabResultApi.enter(orderItem.id, {results})
      notification.success({message: t('Results saved successfully')})
      onSaved()
      onClose()
    } catch (e: any) {
      notification.error({
        message: t('Failed to save results'),
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <Modal
      title={t('Enter Results') + ' — ' + (orderItem?.test_name_snapshot || '')}
      open={visible}
      onCancel={onClose}
      onOk={handleOk}
      confirmLoading={submitting}
      okText={t('Save Results')}
      width={600}
      destroyOnClose
    >
      <Spin spinning={loading}>
        {parameters.length === 0 && !loading && (
          <Alert type='info' showIcon message={t('This test has no defined parameters to enter.')} />
        )}
        {parameters.map((p: any) => (
          <div key={p.id} className='row g-2 align-items-center mb-3'>
            <div className='col-md-5'>
              <label>{p.parameter_name}</label>
            </div>
            <div className='col-md-5'>
              <Input
                value={values[p.id] || ''}
                onChange={(e) => setValues({...values, [p.id]: e.target.value})}
                placeholder={p.unit || ''}
              />
            </div>
            <div className='col-md-2 text-muted'>{p.unit}</div>
          </div>
        ))}
      </Spin>
    </Modal>
  )
}

export default EnterResultsModal

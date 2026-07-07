import React, {FC, useEffect, useState} from 'react'
import {Modal, Form, Select, Input, AutoComplete, notification} from 'antd'
import {LabOrderApi, LabTestApi, PatientApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input
const {Option} = Select

interface PatientOption {
  id: number
  full_name: string
  primary_phone: string
  mrn: number
}

interface PlaceLabOrderModalProps {
  visible: boolean
  onClose: () => void
  onCreated: () => void
  defaultPatientId?: number
  defaultPatientLabel?: string
  lockPatient?: boolean
  opdVisitId?: number
  ipdAdmissionId?: number
}

const PlaceLabOrderModal: FC<PlaceLabOrderModalProps> = (props) => {
  const {visible, onClose, onCreated, defaultPatientId, defaultPatientLabel, lockPatient, opdVisitId, ipdAdmissionId} =
    props
  const {t} = useLang()
  const [form] = Form.useForm()
  const [submitting, setSubmitting] = useState(false)
  const [patientOptions, setPatientOptions] = useState<PatientOption[]>([])
  const [patientSearch, setPatientSearch] = useState('')
  const [labTests, setLabTests] = useState<any[]>([])

  useEffect(() => {
    if (visible) {
      LabTestApi.dropdown({status: 1})
        .then((res: any) => setLabTests(res?.data?.results || res?.data || []))
        .catch(() => setLabTests([]))
      form.resetFields()
      if (defaultPatientId) {
        form.setFieldsValue({patient_id: defaultPatientId})
      }
    }
  }, [visible])

  useEffect(() => {
    if (!patientSearch || patientSearch.length < 2) {
      setPatientOptions([])
      return
    }
    PatientApi.getByWhere({$search: patientSearch, $top: 10})
      .then((res: any) => setPatientOptions(res?.data?.results || []))
      .catch(() => setPatientOptions([]))
  }, [patientSearch])

  const handleOk = () => {
    form.validateFields().then(async (values) => {
      setSubmitting(true)
      try {
        const payload: any = {
          patient_id: values.patient_id,
          priority: values.priority,
          clinical_indication: values.clinical_indication,
          items: (values.lab_test_ids || []).map((id: any) => ({lab_test_id: id})),
        }
        if (opdVisitId) payload.opd_visit_id = opdVisitId
        if (ipdAdmissionId) payload.ipd_admission_id = ipdAdmissionId

        await LabOrderApi.create(payload)
        notification.success({message: t('Lab order placed successfully')})
        onCreated()
        onClose()
      } catch (e: any) {
        notification.error({
          message: t('Failed to place lab order'),
          description: e?.response?.data?.message || e?.message || 'Unknown error',
        })
      } finally {
        setSubmitting(false)
      }
    })
  }

  return (
    <Modal
      title={t('Place Lab Order')}
      open={visible}
      onCancel={onClose}
      onOk={handleOk}
      confirmLoading={submitting}
      okText={t('Place Order')}
      width={640}
      destroyOnClose
    >
      <Form form={form} layout='vertical' initialValues={{priority: 'routine'}}>
        {!lockPatient ? (
          <Form.Item name='patient_id' label={t('Patient')} rules={[{required: true, message: t('Please select a patient')}]}>
            <AutoComplete
              placeholder={t('Search by name, phone or MRN')}
              onSearch={setPatientSearch}
              onSelect={(_value: any, option: any) => form.setFieldsValue({patient_id: option.id})}
              allowClear
            >
              {patientOptions.map((p) => (
                <AutoComplete.Option key={p.id} value={p.id}>
                  <div>
                    <strong>{p.full_name}</strong> <span className='text-muted'>(MRN {p.mrn})</span>
                  </div>
                  <div className='text-muted fs-7'>{p.primary_phone}</div>
                </AutoComplete.Option>
              ))}
            </AutoComplete>
          </Form.Item>
        ) : (
          <Form.Item label={t('Patient')}>
            <Input disabled value={defaultPatientLabel} />
            <Form.Item name='patient_id' hidden>
              <Input />
            </Form.Item>
          </Form.Item>
        )}

        <Form.Item name='priority' label={t('Priority')}>
          <Select>
            <Option value='routine'>{t('Routine')}</Option>
            <Option value='urgent'>{t('Urgent')}</Option>
            <Option value='stat'>{t('STAT')}</Option>
          </Select>
        </Form.Item>

        <Form.Item
          name='lab_test_ids'
          label={t('Tests')}
          rules={[{required: true, message: t('Select at least one test')}]}
        >
          <Select mode='multiple' placeholder={t('Select lab tests')} optionFilterProp='children'>
            {labTests.map((lt: any) => (
              <Option key={lt.id} value={lt.id}>
                {lt.name || lt.text}
              </Option>
            ))}
          </Select>
        </Form.Item>

        <Form.Item name='clinical_indication' label={t('Clinical Indication')}>
          <TextArea rows={3} />
        </Form.Item>
      </Form>
    </Modal>
  )
}

export default PlaceLabOrderModal

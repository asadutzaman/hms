import React, {FC, useEffect, useState} from 'react'
import {Modal, Form, Select, Input, InputNumber, AutoComplete, notification} from 'antd'
import {PreAuthorizationApi, InsuranceCompanyApi, InsuranceSchemeApi, PatientApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input
const {Option} = Select

interface PatientOption {
  id: number
  full_name: string
  primary_phone: string
  mrn: number
}

interface SubmitPreAuthModalProps {
  visible: boolean
  onClose: () => void
  onCreated: () => void
}

const SubmitPreAuthModal: FC<SubmitPreAuthModalProps> = ({visible, onClose, onCreated}) => {
  const {t} = useLang()
  const [form] = Form.useForm()
  const [submitting, setSubmitting] = useState(false)
  const [patientOptions, setPatientOptions] = useState<PatientOption[]>([])
  const [patientSearch, setPatientSearch] = useState('')
  const [companies, setCompanies] = useState<any[]>([])
  const [schemes, setSchemes] = useState<any[]>([])
  const [selectedCompanyId, setSelectedCompanyId] = useState<any>(null)

  useEffect(() => {
    if (visible) {
      form.resetFields()
      setSchemes([])
      setSelectedCompanyId(null)
      InsuranceCompanyApi.dropdown({status: 1})
        .then((res: any) => setCompanies(res?.data?.results || res?.data || []))
        .catch(() => setCompanies([]))
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

  const handleCompanyChange = (companyId: any) => {
    setSelectedCompanyId(companyId)
    form.setFieldsValue({insurance_scheme_id: null})
    InsuranceSchemeApi.byCompany(companyId)
      .then((res: any) => setSchemes(res?.data?.data ?? res?.data ?? []))
      .catch(() => setSchemes([]))
  }

  const handleOk = () => {
    form.validateFields().then(async (values) => {
      setSubmitting(true)
      try {
        await PreAuthorizationApi.create(values)
        notification.success({message: t('Pre-authorization submitted successfully')})
        onCreated()
        onClose()
      } catch (e: any) {
        notification.error({
          message: t('Failed to submit pre-authorization'),
          description: e?.response?.data?.message || e?.message || 'Unknown error',
        })
      } finally {
        setSubmitting(false)
      }
    })
  }

  return (
    <Modal
      title={t('Submit Pre-Authorization Request')}
      open={visible}
      onCancel={onClose}
      onOk={handleOk}
      confirmLoading={submitting}
      okText={t('Submit')}
      width={640}
      destroyOnClose
    >
      <Form form={form} layout='vertical'>
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

        <Form.Item name='insurance_company_id' label={t('Insurance Company')} rules={[{required: true}]}>
          <Select placeholder={t('Select Insurance Company')} onChange={handleCompanyChange}>
            {companies.map((c: any) => (
              <Option key={c.id} value={c.id}>
                {c.name || c.text}
              </Option>
            ))}
          </Select>
        </Form.Item>

        <Form.Item name='insurance_scheme_id' label={t('Scheme')}>
          <Select placeholder={t('Select Scheme (optional)')} allowClear disabled={!selectedCompanyId}>
            {schemes.map((s: any) => (
              <Option key={s.id} value={s.id}>
                {s.name}
              </Option>
            ))}
          </Select>
        </Form.Item>

        <Form.Item name='policy_number' label={t('Policy Number')}>
          <Input />
        </Form.Item>

        <Form.Item name='estimated_amount' label={t('Estimated Amount')} rules={[{required: true}]}>
          <InputNumber min={0} precision={2} style={{width: '100%'}} />
        </Form.Item>

        <Form.Item name='diagnosis' label={t('Diagnosis')}>
          <TextArea rows={2} />
        </Form.Item>

        <Form.Item name='treatment_plan' label={t('Treatment Plan')}>
          <TextArea rows={2} />
        </Form.Item>
      </Form>
    </Modal>
  )
}

export default SubmitPreAuthModal

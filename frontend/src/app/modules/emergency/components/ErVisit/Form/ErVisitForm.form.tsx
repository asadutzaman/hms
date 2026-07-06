import React, {FC, useState} from 'react'
import {Form, Input, Select, AutoComplete, Row, Col} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import {PatientApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input
const {Option} = Select

interface PatientOption {
  id: number
  first_name?: string
  last_name?: string
  full_name?: string
  primary_phone?: string
  mrn?: string
}

const ErVisitAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()

  const [patientOptions, setPatientOptions] = useState<PatientOption[]>([])
  const [patientSearch, setPatientSearch] = useState('')

  const watchedPatientId = Form.useWatch('patient_id', formRef)

  React.useEffect(() => {
    if (!patientSearch || patientSearch.length < 2) {
      setPatientOptions([])
      return
    }
    PatientApi.getByWhere({$search: patientSearch, $top: 10})
      .then((res: any) => setPatientOptions(res?.data?.results || []))
      .catch(() => setPatientOptions([]))
  }, [patientSearch])

  const patientLabel = (p: PatientOption) => p.full_name || `${p.first_name || ''} ${p.last_name || ''}`.trim()

  return (
    <div className='form-page-content pe-3'>
      <Form
        layout='vertical'
        form={formRef}
        name='erVisitForm'
        scrollToFirstError={true}
        initialValues={initialValues}
        onValuesChange={handleChange}
        onFinish={handleSubmit}
        onFinishFailed={handleSubmitFailed}
      >
        <Row gutter={16}>
          <Col span={24}>
            <Form.Item name='patient_id' label={t('Patient')} rules={rules.required}>
              <AutoComplete
                placeholder={t('Search by name, phone or MRN')}
                onSearch={setPatientSearch}
                onSelect={(_value: any, option: any) => formRef.setFieldsValue({patient_id: option.id})}
                allowClear
              >
                {patientOptions.map((p) => (
                  <AutoComplete.Option key={p.id} value={p.id}>
                    <div>
                      <strong>{patientLabel(p)}</strong> {p.mrn && <span className='text-muted'>(MRN {p.mrn})</span>}
                    </div>
                    {p.primary_phone && <div className='text-muted fs-7'>{p.primary_phone}</div>}
                  </AutoComplete.Option>
                ))}
              </AutoComplete>
            </Form.Item>
            {watchedPatientId && (
              <div className='text-muted fs-7 mb-3'>
                {t('Selected patient ID')}: <strong>{watchedPatientId}</strong>
              </div>
            )}
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={24}>
            <Form.Item name='arrival_mode' label={t('Arrival Mode')}>
              <Select placeholder={t('Select arrival mode')}>
                <Option value='walk_in'>{t('Walk-in')}</Option>
                <Option value='ambulance'>{t('Ambulance')}</Option>
                <Option value='referred'>{t('Referred')}</Option>
                <Option value='police'>{t('Police')}</Option>
                <Option value='other'>{t('Other')}</Option>
              </Select>
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={24}>
            <Form.Item name='chief_complaint' label={t('Chief Complaint')} rules={rules.required}>
              <TextArea rows={3} placeholder={t('e.g. Chest pain, shortness of breath')} />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </div>
  )
}

export default React.memo(ErVisitAddOrEditForm)

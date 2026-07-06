import React, {FC, useEffect, useState} from 'react'
import {Form, Input, Select, DatePicker, AutoComplete, Row, Col, Alert} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import {PatientApi, EmployeeApi, DepartmentApi, BedApi} from 'src/app/api'
import WardSelect from 'src/app/components/Dropdown/WardSelect'
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

interface EmployeeOption {
  id: number
  name_en?: string
  designation_name?: string
}

const IpdAdmissionAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed, isNewRecord, itemData} = props
  const {t} = useLang()

  const [patientOptions, setPatientOptions] = useState<PatientOption[]>([])
  const [patientSearch, setPatientSearch] = useState('')
  const [doctorOptions, setDoctorOptions] = useState<EmployeeOption[]>([])
  const [doctorSearch, setDoctorSearch] = useState('')
  const [departments, setDepartments] = useState<any[]>([])
  const [beds, setBeds] = useState<any[]>([])
  const [loadingBeds, setLoadingBeds] = useState(false)

  const watchedWardId = Form.useWatch('ward_id', formRef)
  const watchedPatientId = Form.useWatch('patient_id', formRef)
  const watchedDoctorId = Form.useWatch('attending_doctor_id', formRef)

  useEffect(() => {
    DepartmentApi.dropdown({status: 1})
      .then((res: any) => setDepartments(res?.data?.results || []))
      .catch(() => setDepartments([]))
  }, [])

  useEffect(() => {
    if (!patientSearch || patientSearch.length < 2) {
      setPatientOptions([])
      return
    }
    PatientApi.getByWhere({$search: patientSearch, $top: 10})
      .then((res: any) => setPatientOptions(res?.data?.results || []))
      .catch(() => setPatientOptions([]))
  }, [patientSearch])

  useEffect(() => {
    if (!doctorSearch || doctorSearch.length < 2) {
      setDoctorOptions([])
      return
    }
    EmployeeApi.list({$search: doctorSearch, $top: 10})
      .then((res: any) => setDoctorOptions(res?.data?.results || []))
      .catch(() => setDoctorOptions([]))
  }, [doctorSearch])

  useEffect(() => {
    if (!isNewRecord || !watchedWardId) {
      setBeds([])
      return
    }
    setLoadingBeds(true)
    BedApi.list({$filter: `ward_id=${watchedWardId}`, $top: 200})
      .then((res: any) => {
        const all = res?.data?.results || []
        setBeds(all.filter((b: any) => b.ward_id === watchedWardId && b.bed_status === 'vacant'))
      })
      .catch(() => setBeds([]))
      .finally(() => setLoadingBeds(false))
  }, [watchedWardId, isNewRecord])

  const patientLabel = (p: PatientOption) =>
    p.full_name || `${p.first_name || ''} ${p.last_name || ''}`.trim()

  return (
    <div className='form-page-content pe-3'>
      <Form
        layout='vertical'
        form={formRef}
        name='ipdAdmissionForm'
        scrollToFirstError={true}
        initialValues={initialValues}
        onValuesChange={handleChange}
        onFinish={handleSubmit}
        onFinishFailed={handleSubmitFailed}
      >
        {isNewRecord ? (
          <>
            <Row gutter={16}>
              <Col span={24}>
                <Form.Item
                  name='patient_id'
                  label={t('Patient')}
                  rules={rules.required}
                >
                  <AutoComplete
                    placeholder={t('Search by name, phone or MRN')}
                    onSearch={setPatientSearch}
                    onSelect={(_value: any, option: any) => {
                      formRef.setFieldsValue({patient_id: option.id})
                    }}
                    allowClear
                  >
                    {patientOptions.map((p) => (
                      <AutoComplete.Option key={p.id} value={p.id}>
                        <div>
                          <strong>{patientLabel(p)}</strong>{' '}
                          {p.mrn && <span className='text-muted'>(MRN {p.mrn})</span>}
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
              <Col md={8} xs={24}>
                <Form.Item name='admission_type' label={t('Admission Type')} rules={rules.required}>
                  <Select placeholder={t('Select Type')}>
                    <Option value='emergency'>{t('Emergency')}</Option>
                    <Option value='planned'>{t('Planned')}</Option>
                  </Select>
                </Form.Item>
              </Col>
              <Col md={8} xs={24}>
                <Form.Item name='admission_date' label={t('Admission Date')}>
                  <DatePicker showTime format='YYYY-MM-DD HH:mm' style={{width: '100%'}} />
                </Form.Item>
              </Col>
              <Col md={8} xs={24}>
                <Form.Item name='expected_discharge_date' label={t('Expected Discharge Date')}>
                  <DatePicker format='YYYY-MM-DD' style={{width: '100%'}} />
                </Form.Item>
              </Col>
            </Row>

            <Row gutter={16}>
              <Col md={12} xs={24}>
                <Form.Item name='ward_id' label={t('Ward')} rules={rules.required}>
                  <WardSelect
                    wardId={formRef.getFieldValue('ward_id')}
                    placeholder={t('Select Ward')}
                    onSelect={(value: any) => formRef.setFieldsValue({ward_id: value, bed_id: null})}
                    onChange={(value: any) => formRef.setFieldsValue({ward_id: value, bed_id: null})}
                  />
                </Form.Item>
              </Col>
              <Col md={12} xs={24}>
                <Form.Item name='bed_id' label={t('Bed')} rules={rules.required}>
                  <Select
                    placeholder={
                      !watchedWardId
                        ? t('Select ward first')
                        : loadingBeds
                        ? t('Loading beds…')
                        : beds.length
                        ? t('Select vacant bed')
                        : t('No vacant beds in this ward')
                    }
                    loading={loadingBeds}
                    disabled={!watchedWardId}
                    allowClear
                  >
                    {beds.map((b: any) => (
                      <Option key={b.id} value={b.id}>
                        {b.bed_number} {b.bed_type ? `(${b.bed_type})` : ''} — {b.daily_rate}/night
                      </Option>
                    ))}
                  </Select>
                </Form.Item>
              </Col>
            </Row>
          </>
        ) : (
          <>
            <Alert
              type='info'
              showIcon
              className='mb-4'
              message={
                <span>
                  <strong>{itemData?.admission_no}</strong> — {itemData?.patient_name} · {itemData?.ward_name} / Bed{' '}
                  {itemData?.bed_number} · <span className='text-capitalize'>{itemData?.admission_type}</span>
                </span>
              }
              description={t('Patient, ward, bed, admission type and admission date cannot be changed here. Use the "Transfer Bed" action from the admission view to move the patient to a different bed.')}
            />
          </>
        )}

        <Row gutter={16}>
          <Col md={12} xs={24}>
            <Form.Item name='attending_doctor_id' label={t('Attending Doctor')}>
              <AutoComplete
                placeholder={t('Search doctor by name')}
                onSearch={setDoctorSearch}
                onSelect={(_value: any, option: any) => {
                  formRef.setFieldsValue({attending_doctor_id: option.id})
                }}
                allowClear
              >
                {doctorOptions.map((d) => (
                  <AutoComplete.Option key={d.id} value={d.id}>
                    {d.name_en} {d.designation_name ? `— ${d.designation_name}` : ''}
                  </AutoComplete.Option>
                ))}
              </AutoComplete>
            </Form.Item>
            {watchedDoctorId && (
              <div className='text-muted fs-7 mb-3'>
                {t('Selected doctor ID')}: <strong>{watchedDoctorId}</strong>
              </div>
            )}
          </Col>
          <Col md={12} xs={24}>
            <Form.Item name='department_id' label={t('Department')}>
              <Select
                showSearch
                allowClear
                placeholder={t('Select department')}
                optionFilterProp='children'
              >
                {departments.map((dpt: any) => (
                  <Option key={dpt.id} value={dpt.id}>
                    {dpt.name}
                  </Option>
                ))}
              </Select>
            </Form.Item>
          </Col>
        </Row>

        {!isNewRecord && (
          <Row gutter={16}>
            <Col span={24}>
              <Form.Item name='expected_discharge_date' label={t('Expected Discharge Date')}>
                <DatePicker format='YYYY-MM-DD' style={{width: '100%'}} />
              </Form.Item>
            </Col>
          </Row>
        )}

        <Row gutter={16}>
          <Col span={24}>
            <Form.Item name='diagnosis_at_admission' label={t('Diagnosis at Admission')}>
              <TextArea rows={3} placeholder={t('Provisional diagnosis on admission')} />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </div>
  )
}

export default React.memo(IpdAdmissionAddOrEditForm)

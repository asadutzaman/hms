import React, {FC, useEffect, useState} from 'react'
import {
  Form,
  Input,
  Select,
  InputNumber,
  DatePicker,
  TimePicker,
  Switch,
  Tabs,
  Row,
  Col,
  Divider,
  AutoComplete,
} from 'antd'
import {PatientApi, DepartmentApi} from 'src/app/api'
import {OpdVisitApi} from 'src/app/api'
import {DateTimeUtils, Message} from 'src/app/utils'

const {TextArea} = Input
const {Option} = Select
const {TabPane} = Tabs

interface PatientOption {
  id: number
  full_name: string
  primary_phone: string
  mrn: number
  patient_no: string
}

interface SlotOption {
  id: number
  start_time: string
  end_time: string
  available: boolean
  label?: string
}

const OpdVisitAddOrEditForm: FC<any> = ({formRef, isNewRecord}) => {
  const [patientOptions, setPatientOptions] = useState<PatientOption[]>([])
  const [patientSearch, setPatientSearch] = useState<string>('')
  const [departments, setDepartments] = useState<any[]>([])
  const [submittingInlinePatient, setSubmittingInlinePatient] = useState(false)

  const watchedDoctorId = Form.useWatch('doctor_id', formRef)
  const watchedDepartmentId = Form.useWatch('department_id', formRef)
  const watchedIsNewPatient = Form.useWatch('use_new_patient', formRef)
  const watchedPatientId = Form.useWatch('patient_id', formRef)

  useEffect(() => {
    DepartmentApi.dropdown({status: 1})
      .then((res: any) => setDepartments(res?.data?.results || []))
      .catch(() => setDepartments([]))
  }, [])

  // Patient search (debounced simple)
  useEffect(() => {
    if (!patientSearch || patientSearch.length < 2) {
      setPatientOptions([])
      return
    }
    PatientApi.getByWhere({$search: patientSearch, $top: 10})
      .then((res: any) => setPatientOptions(res?.data?.results || []))
      .catch(() => setPatientOptions([]))
  }, [patientSearch])

  return (
    <div className='form-page-content'>
      <Tabs defaultActiveKey='patient'>
        {/* ============= PATIENT TAB ============= */}
        <TabPane tab='Patient' key='patient'>
          <Row gutter={[16, 16]}>
            <Col span={24}>
              <Form.Item name='use_new_patient' label='Patient Type' valuePropName='checked'>
                <Switch
                  checkedChildren='New Patient'
                  unCheckedChildren='Existing Patient'
                />
              </Form.Item>
            </Col>

            {!watchedIsNewPatient && (
              <>
                <Col md={12} xs={24}>
                  <Form.Item
                    name='patient_id'
                    label='Search Patient'
                    rules={[
                      {
                        required: !watchedIsNewPatient,
                        message: 'Please select a patient',
                      },
                    ]}
                  >
                    <AutoComplete
                      placeholder='Search by name, phone or MRN'
                      onSearch={setPatientSearch}
                      onSelect={(_value: any, option: any) => {
                        formRef.setFieldsValue({patient_id: option.id})
                      }}
                      allowClear
                    >
                      {patientOptions.map((p) => (
                        <AutoComplete.Option key={p.id} value={p.id}>
                          <div>
                            <strong>{p.full_name}</strong>{' '}
                            <span className='text-muted'>({p.patient_no})</span>
                          </div>
                          <div className='text-muted fs-7'>
                            {p.primary_phone} • MRN {p.mrn}
                          </div>
                        </AutoComplete.Option>
                      ))}
                    </AutoComplete>
                  </Form.Item>
                  {watchedPatientId && patientOptions.length > 0 && (
                    <div className='text-muted fs-7 mb-3'>
                      Selected patient ID: <strong>{watchedPatientId}</strong>
                    </div>
                  )}
                </Col>
              </>
            )}

            {watchedIsNewPatient && (
              <>
                <Col md={8} xs={24}>
                  <Form.Item
                    name='new_patient_first_name'
                    label='First Name'
                    rules={[{required: true, message: 'First name is required'}]}
                  >
                    <Input placeholder='First name' />
                  </Form.Item>
                </Col>
                <Col md={8} xs={24}>
                  <Form.Item name='new_patient_last_name' label='Last Name'>
                    <Input placeholder='Last name' />
                  </Form.Item>
                </Col>
                <Col md={8} xs={24}>
                  <Form.Item
                    name='new_patient_primary_phone'
                    label='Primary Phone'
                    rules={[{required: true, message: 'Phone is required'}]}
                  >
                    <Input placeholder='01XXXXXXXXX' />
                  </Form.Item>
                </Col>
                <Col md={8} xs={24}>
                  <Form.Item
                    name='new_patient_date_of_birth'
                    label='Date of Birth'
                    rules={[{required: true, message: 'DOB is required'}]}
                  >
                    <DatePicker
                      format='YYYY-MM-DD'
                      style={{width: '100%'}}
                    />
                  </Form.Item>
                </Col>
                <Col md={8} xs={24}>
                  <Form.Item
                    name='new_patient_gender'
                    label='Gender'
                    rules={[{required: true, message: 'Gender is required'}]}
                  >
                    <Select placeholder='Select gender'>
                      <Option value='male'>Male</Option>
                      <Option value='female'>Female</Option>
                      <Option value='other'>Other</Option>
                      <Option value='unknown'>Unknown</Option>
                    </Select>
                  </Form.Item>
                </Col>
              </>
            )}
          </Row>
        </TabPane>

        {/* ============= SCHEDULE TAB ============= */}
        <TabPane tab='Schedule' key='schedule'>
          <Row gutter={[16, 16]}>
            <Col md={12} xs={24}>
              <Form.Item
                name='department_id'
                label='Department'
                rules={[{required: true, message: 'Department is required'}]}
              >
                <Select
                  showSearch
                  placeholder='Select department'
                  optionFilterProp='children'
                  filterOption={(input, option: any) =>
                    option?.children
                      .toLowerCase()
                      .indexOf(input.toLowerCase()) >= 0
                  }
                >
                  {departments.map((d: any) => (
                    <Option key={d.id} value={d.id}>
                      {d.name}
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>

            <Col md={12} xs={24}>
              <Form.Item
                name='doctor_id'
                label='Doctor'
                rules={[{required: true, message: 'Doctor is required'}]}
              >
                <Select
                  showSearch
                  placeholder='Select doctor'
                  optionFilterProp='children'
                  filterOption={(input, option: any) =>
                    option?.children
                      .toLowerCase()
                      .indexOf(input.toLowerCase()) >= 0
                  }
                >
                  {/* Options populated from DoctorScheduleApi.byDoctor */}
                  <Option value={1}>Dr. Sample</Option>
                </Select>
              </Form.Item>
            </Col>

            <Col md={8} xs={24}>
              <Form.Item
                name='visit_date'
                label='Visit Date'
                rules={[{required: true, message: 'Date is required'}]}
              >
                <DatePicker format='YYYY-MM-DD' style={{width: '100%'}} />
              </Form.Item>
            </Col>

            <Col md={6} xs={24}>
              <Form.Item
                name='consultation_mode'
                label='Consultation Mode'
                rules={[{required: true}]}
              >
                <Select>
                  <Option value='in_person'>In Person</Option>
                  <Option value='telemedicine'>Telemedicine</Option>
                  <Option value='home_visit'>Home Visit</Option>
                </Select>
              </Form.Item>
            </Col>

            <Col md={6} xs={24}>
              <Form.Item name='source' label='Source' rules={[{required: true}]}>
                <Select>
                  <Option value='online'>Online</Option>
                  <Option value='walk_in'>Walk-in</Option>
                  <Option value='phone'>Phone</Option>
                  <Option value='referral'>Referral</Option>
                  <Option value='follow_up'>Follow-up</Option>
                </Select>
              </Form.Item>
            </Col>

            <Col md={6} xs={24}>
              <Form.Item name='consultation_fee' label='Consultation Fee'>
                <InputNumber
                  min={0}
                  style={{width: '100%'}}
                  placeholder='0.00'
                />
              </Form.Item>
            </Col>

            <Col md={6} xs={24}>
              <Form.Item name='follow_up_fee' label='Follow-up Fee'>
                <InputNumber
                  min={0}
                  style={{width: '100%'}}
                  placeholder='0.00'
                />
              </Form.Item>
            </Col>
          </Row>
        </TabPane>

        {/* ============= NOTES TAB ============= */}
        <TabPane tab='Notes' key='notes'>
          <Row gutter={[16, 16]}>
            <Col span={24}>
              <Form.Item
                name='reason'
                label='Reason for Visit'
                rules={[{required: true, message: 'Reason is required'}]}
              >
                <Input placeholder='e.g. Chest pain follow-up' />
              </Form.Item>
            </Col>

            <Col span={24}>
              <Form.Item name='symptoms' label='Symptoms'>
                <TextArea rows={2} placeholder='Current symptoms' />
              </Form.Item>
            </Col>

            <Col span={24}>
              <Form.Item name='notes' label='Patient Notes'>
                <TextArea rows={3} placeholder='Notes visible to patient' />
              </Form.Item>
            </Col>

            <Col span={24}>
              <Form.Item
                name='internal_notes'
                label='Internal Notes'
                tooltip='Visible only to staff'
              >
                <TextArea rows={3} placeholder='Internal notes' />
              </Form.Item>
            </Col>

            <Col md={12} xs={24}>
              <Form.Item
                name='referral_doctor_id'
                label='Referral Doctor ID'
                tooltip='If this is a referral, enter referring doctor ID'
              >
                <InputNumber
                  min={1}
                  style={{width: '100%'}}
                  placeholder='Doctor ID'
                />
              </Form.Item>
            </Col>

            <Col span={24}>
              <Form.Item name='referral_notes' label='Referral Notes'>
                <TextArea rows={2} placeholder='Referral context' />
              </Form.Item>
            </Col>

            <Col md={8} xs={24}>
              <Form.Item
                name='is_follow_up'
                label='Follow-up Visit'
                valuePropName='checked'
              >
                <Switch />
              </Form.Item>
            </Col>

            <Col md={8} xs={24}>
              <Form.Item
                name='parent_OpdVisit_id'
                label='Parent OpdVisit ID'
                tooltip='If this is a follow-up, the original OpdVisit ID'
              >
                <InputNumber
                  min={1}
                  style={{width: '100%'}}
                  placeholder='Original OpdVisit ID'
                />
              </Form.Item>
            </Col>

            <Col md={8} xs={24}>
              <Form.Item name='status' label='Status' valuePropName='checked'>
                <Switch
                  checkedChildren='Active'
                  unCheckedChildren='Inactive'
                  defaultChecked
                />
              </Form.Item>
            </Col>

            <Col span={24}>
              <Divider plain>Notifications</Divider>
            </Col>

            <Col md={8} xs={24}>
              <Form.Item
                name='send_sms_reminder'
                label='Send SMS Reminder'
                valuePropName='checked'
              >
                <Switch defaultChecked />
              </Form.Item>
            </Col>

            <Col md={8} xs={24}>
              <Form.Item
                name='send_email_reminder'
                label='Send Email Reminder'
                valuePropName='checked'
              >
                <Switch />
              </Form.Item>
            </Col>
          </Row>
        </TabPane>
      </Tabs>
    </div>
  )
}

export default React.memo(OpdVisitAddOrEditForm)

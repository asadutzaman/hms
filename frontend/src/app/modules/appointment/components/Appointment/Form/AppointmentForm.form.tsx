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
import dayjs from 'dayjs'
import {PatientApi} from 'src/app/api'
import {DoctorScheduleApi} from 'src/app/api'
import {DateTimeUtils} from 'src/app/utils'
import DepartmentDoctorDependentSelect from 'src/app/components/Dropdown/Dependent/DepartmentDoctorDependentSelect'

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

// DrawerForm does NOT provide a <Form> wrapper — each form component owns its
// own <Form form={formRef}>. Without it the fields never register and
// formRef.submit() (the drawer's Submit button) does nothing.
const AppointmentAddOrEditForm: FC<any> = ({
  formRef,
  initialValues,
  handleChange,
  handleSubmit,
  handleSubmitFailed,
}) => {
  const [patientOptions, setPatientOptions] = useState<PatientOption[]>([])
  const [selectedPatient, setSelectedPatient] = useState<PatientOption | null>(null)
  const [patientSearch, setPatientSearch] = useState<string>('')
  const [availableSlots, setAvailableSlots] = useState<SlotOption[]>([])
  const [loadingSlots, setLoadingSlots] = useState(false)

  const watchedDoctorId = Form.useWatch('doctor_id', formRef)
  const watchedDepartmentId = Form.useWatch('department_id', formRef)
  const watchedDate = Form.useWatch('appointment_date', formRef)
  const watchedMode = Form.useWatch('consultation_mode', formRef)
  const watchedIsNewPatient = Form.useWatch('use_new_patient', formRef)
  const watchedPatientId = Form.useWatch('patient_id', formRef)

  // Fetch the doctor's open slots for the chosen date. Slots come from the
  // doctor's schedule (materialized on demand); keyed by the doctor's user id.
  useEffect(() => {
    if (watchedDoctorId && watchedDate) {
      setLoadingSlots(true)
      DoctorScheduleApi.availableSlots({
        doctor_id: watchedDoctorId,
        date: DateTimeUtils.formatDate(watchedDate),
      })
        .then((res: any) => {
          const list = Array.isArray(res?.data) ? res.data : res?.data?.data ?? res?.data?.results ?? []
          setAvailableSlots(
            list.map((s: any) => ({
              id: s.id,
              start_time: String(s.start_time || '').slice(0, 5),
              end_time: String(s.end_time || '').slice(0, 5),
              available: s.status === 'open' && !s.is_blocked,
            }))
          )
        })
        .catch(() => setAvailableSlots([]))
        .finally(() => setLoadingSlots(false))
    } else {
      setAvailableSlots([])
    }
  }, [watchedDoctorId, watchedDate])

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

  const handleSlotChange = (slotId: number) => {
    const slot = availableSlots.find((s) => s.id === slotId)
    if (slot) {
      formRef.setFieldsValue({
        start_time: slot.start_time,
        end_time: slot.end_time,
        duration_minutes:
          timeDiffMinutes(slot.start_time, slot.end_time),
      })
    }
  }

  const timeDiffMinutes = (start: string, end: string): number => {
    try {
      const [sh, sm] = start.split(':').map(Number)
      const [eh, em] = end.split(':').map(Number)
      return eh * 60 + em - (sh * 60 + sm)
    } catch {
      return 0
    }
  }

  return (
    <div className='form-page-content'>
      <Form
        form={formRef}
        layout='vertical'
        name='appointmentForm'
        scrollToFirstError
        initialValues={initialValues}
        onValuesChange={handleChange}
        onFinish={handleSubmit}
        onFinishFailed={handleSubmitFailed}
      >
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
                    <Select
                      showSearch
                      allowClear
                      value={watchedPatientId ?? undefined}
                      placeholder='Search by name, phone or MRN'
                      filterOption={false}
                      optionLabelProp='label'
                      notFoundContent={null}
                      onSearch={setPatientSearch}
                      onChange={(value: any) => {
                        formRef.setFieldsValue({patient_id: value ?? null})
                        const picked = patientOptions.find((p) => p.id === value)
                        setSelectedPatient(picked || null)
                      }}
                    >
                      {(selectedPatient && !patientOptions.some((p) => p.id === selectedPatient.id)
                        ? [selectedPatient, ...patientOptions]
                        : patientOptions
                      ).map((p) => {
                        const name =
                          p.full_name || [(p as any).first_name, (p as any).last_name].filter(Boolean).join(' ')
                        const code = p.patient_no || p.mrn
                        return (
                          <Option key={p.id} value={p.id} label={code ? `${name} (${code})` : name}>
                            <div>
                              <strong>{name}</strong>{' '}
                              {code && <span className='text-muted'>({code})</span>}
                            </div>
                            <div className='text-muted fs-7'>
                              {p.primary_phone}
                              {p.mrn ? ` • MRN ${p.mrn}` : ''}
                            </div>
                          </Option>
                        )
                      })}
                    </Select>
                  </Form.Item>
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
            {/* Department drives the doctor list (dependent dropdown). */}
            <DepartmentDoctorDependentSelect
              formRef={formRef}
              departmentProps={{
                fieldName: 'department_id',
                fieldLabel: 'Department',
                rules: [{required: true, message: 'Department is required'}],
                gridCol: {md: 12, xs: 24},
              }}
              doctorProps={{
                fieldName: 'doctor_id',
                fieldLabel: 'Doctor',
                rules: [{required: true, message: 'Doctor is required'}],
                gridCol: {md: 12, xs: 24},
              }}
            />

            <Col md={8} xs={24}>
              <Form.Item
                name='appointment_date'
                label='Appointment Date'
                rules={[{required: true, message: 'Date is required'}]}
                getValueProps={(v) => ({value: v ? dayjs(v) : null})}
                normalize={(v) => (v ? dayjs(v).format('YYYY-MM-DD') : null)}
              >
                <DatePicker format='YYYY-MM-DD' style={{width: '100%'}} />
              </Form.Item>
            </Col>

            <Col md={8} xs={24}>
              <Form.Item
                name='appointment_slot_id'
                label='Available Slot'
                tooltip='Pick a slot to auto-fill start/end times'
              >
                <Select
                  placeholder={
                    loadingSlots
                      ? 'Loading slots…'
                      : availableSlots.length
                      ? 'Select a slot'
                      : 'Select doctor and date first'
                  }
                  loading={loadingSlots}
                  onChange={handleSlotChange}
                  allowClear
                  disabled={!availableSlots.length && !loadingSlots}
                >
                  {availableSlots.map((s) => (
                    <Option
                      key={s.id}
                      value={s.id}
                      disabled={!s.available}
                    >
                      {s.start_time} - {s.end_time}
                      {s.label ? ` (${s.label})` : ''}
                      {!s.available ? ' — booked' : ''}
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>

            <Col md={4} xs={24}>
              <Form.Item name='start_time' label='Start Time'>
                <Input placeholder='HH:MM' />
              </Form.Item>
            </Col>

            <Col md={4} xs={24}>
              <Form.Item name='end_time' label='End Time'>
                <Input placeholder='HH:MM' />
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
                name='reason_for_visit'
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
                name='parent_appointment_id'
                label='Parent Appointment ID'
                tooltip='If this is a follow-up, the original appointment ID'
              >
                <InputNumber
                  min={1}
                  style={{width: '100%'}}
                  placeholder='Original appointment ID'
                />
              </Form.Item>
            </Col>

            <Col md={8} xs={24}>
              {/* status_active is the active/inactive flag. `status` is the
                  appointment lifecycle (pending/confirmed/...) and is managed
                  by the backend — never send a boolean into it. */}
              <Form.Item
                name='status_active'
                label='Status'
                getValueProps={(v) => ({checked: !!v})}
                normalize={(v) => (v ? 1 : 0)}
              >
                <Switch checkedChildren='Active' unCheckedChildren='Inactive' />
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
      </Form>
    </div>
  )
}

export default React.memo(AppointmentAddOrEditForm)

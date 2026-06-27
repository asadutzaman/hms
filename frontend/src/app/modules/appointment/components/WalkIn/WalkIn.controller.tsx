import React, {FC, useEffect, useState} from 'react'
import {Form, notification, AutoComplete, Card, Col, Row, Select} from 'antd'
import {Input, InputNumber, Radio, Switch, Button, Space, Divider} from 'antd'
import {PhoneOutlined, UserOutlined, CalendarOutlined} from '@ant-design/icons'
import {AppointmentApi, PatientApi} from 'src/app/api'
import {HttpService} from 'src/app/services/http.services'
import {CONSTANT_CONFIG} from 'src/app/constants'
import {useNavigate} from 'react-router-dom'

const {Option} = Select
const {TextArea} = Input

const WalkInController: FC<any> = () => {
  const navigate = useNavigate()
  const [form] = Form.useForm()
  const [patientMode, setPatientMode] = useState<'existing' | 'new'>(
    'existing'
  )
  const [patientOptions, setPatientOptions] = useState<any[]>([])
  const [searchingPatient, setSearchingPatient] = useState(false)
  const [selectedPatient, setSelectedPatient] = useState<any>(null)
  const [slots, setSlots] = useState<any[]>([])
  const [loadingSlots, setLoadingSlots] = useState(false)
  const [submitting, setSubmitting] = useState(false)
  const [walkInType, setWalkInType] = useState<'immediate' | 'scheduled'>(
    'immediate'
  )

  const watchedDoctorId = Form.useWatch('doctor_id', form)
  const watchedDepartmentId = Form.useWatch('department_id', form)
  const watchedAppointmentDate = Form.useWatch('appointment_date', form)
  const watchedConsultationMode = Form.useWatch('consultation_mode', form)

  useEffect(() => {
    // Default appointment_date to today
    form.setFieldsValue({
      appointment_date: new Date().toISOString().slice(0, 10),
      consultation_mode: 'in_person',
      send_sms_reminder: false,
      send_email_reminder: false,
    })
  }, [])

  const searchPatient = async (searchText: string) => {
    if (!searchText || searchText.length < 2) {
      setPatientOptions([])
      return
    }
    setSearchingPatient(true)
    try {
      const response: any = await PatientApi.getByWhere({
        $search: searchText,
        $top: 10,
        $select: 'id,patient_code,mrn,first_name,last_name,primary_phone,dob',
      })
      const data = response?.data?.data || response?.data || []
      const options = (Array.isArray(data) ? data : []).map((p: any) => ({
        value: p.id,
        label: `${p.first_name || ''} ${p.last_name || ''} (${p.mrn || p.patient_code || p.primary_phone || ''})`,
        raw: p,
      }))
      setPatientOptions(options)
    } catch (e) {
      console.error('Patient search failed', e)
      setPatientOptions([])
    } finally {
      setSearchingPatient(false)
    }
  }

  const onPatientSelect = (_value: any, option: any) => {
    const p = option.raw
    setSelectedPatient(p)
    form.setFieldsValue({
      patient_id: p.id,
      first_name: p.first_name,
      last_name: p.last_name,
      primary_phone: p.primary_phone,
      gender: p.gender,
      dob: p.dob,
    })
  }

  const fetchAvailableSlots = async () => {
    if (!watchedDoctorId || !watchedAppointmentDate) return
    setLoadingSlots(true)
    try {
      const response: any = await AppointmentApi.availableSlots({
        doctor_id: watchedDoctorId,
        department_id: watchedDepartmentId,
        appointment_date: watchedAppointmentDate,
        consultation_mode: watchedConsultationMode,
      })
      const data = response?.data?.data || response?.data || []
      setSlots(Array.isArray(data) ? data : [])
    } catch (e) {
      console.error('Failed to load slots', e)
      setSlots([])
    } finally {
      setLoadingSlots(false)
    }
  }

  useEffect(() => {
    if (walkInType === 'scheduled') {
      fetchAvailableSlots()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    watchedDoctorId,
    watchedDepartmentId,
    watchedAppointmentDate,
    watchedConsultationMode,
    walkInType,
  ])

  const handleSubmit = async (values: any) => {
    setSubmitting(true)
    try {
      const payload: any = {
        patient_id:
          patientMode === 'existing' ? values.patient_id : undefined,
        new_patient:
          patientMode === 'new'
            ? {
                first_name: values.first_name,
                last_name: values.last_name,
                primary_phone: values.primary_phone,
                dob: values.dob,
                gender: values.gender,
              }
            : undefined,
        doctor_id: values.doctor_id,
        department_id: values.department_id,
        chamber_id: values.chamber_id,
        appointment_date: values.appointment_date,
        start_time:
          walkInType === 'immediate' ? values.start_time : values.slot_start,
        end_time:
          walkInType === 'immediate' ? values.end_time : values.slot_end,
        duration_minutes: values.duration_minutes,
        consultation_mode: values.consultation_mode || 'in_person',
        appointment_source: 'walk_in',
        reason: values.reason,
        notes: values.notes,
        consultation_fee: values.consultation_fee,
        send_sms_reminder: !!values.send_sms_reminder,
        send_email_reminder: !!values.send_email_reminder,
        status: walkInType === 'immediate' ? 'checked_in' : 'scheduled',
      }

      const response: any = await HttpService.post(
        `${CONSTANT_CONFIG.SERVER_PREFIX}/appointment/walk-in`,
        payload,
        {},
        {}
      )

      notification.success({
        message: 'Walk-in Registered',
        description:
          walkInType === 'immediate'
            ? 'Patient checked in and added to queue.'
            : 'Walk-in appointment scheduled.',
      })

      form.resetFields()
      setSelectedPatient(null)
      setSlots([])

      if (walkInType === 'immediate') {
        navigate('/appointment/queue')
      } else {
        navigate('/appointment/list')
      }
    } catch (e: any) {
      notification.error({
        message: 'Failed to register walk-in',
        description:
          e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className='card p-4'>
      <h2 className='mb-1'>Walk-in Registration</h2>
      <p className='text-muted'>
        Quick-register a patient who arrives without a prior appointment
      </p>

      <Form form={form} layout='vertical' onFinish={handleSubmit}>
        <Card title='Visit Type' className='mb-3'>
          <Radio.Group
            value={walkInType}
            onChange={(e) => setWalkInType(e.target.value)}
          >
            <Radio.Button value='immediate'>
              Immediate (check-in to queue now)
            </Radio.Button>
            <Radio.Button value='scheduled'>
              Scheduled (book for later today)
            </Radio.Button>
          </Radio.Group>
        </Card>

        <Card title='Patient' className='mb-3'>
          <Form.Item label='Patient Type'>
            <Radio.Group
              value={patientMode}
              onChange={(e) => {
                setPatientMode(e.target.value)
                form.setFieldsValue({
                  patient_id: undefined,
                  first_name: undefined,
                  last_name: undefined,
                  primary_phone: undefined,
                  gender: undefined,
                  dob: undefined,
                })
                setSelectedPatient(null)
              }}
            >
              <Radio value='existing'>Existing patient</Radio>
              <Radio value='new'>New patient</Radio>
            </Radio.Group>
          </Form.Item>

          {patientMode === 'existing' ? (
            <>
              <Form.Item
                label='Search Patient'
                name='patient_search'
                rules={[
                  {required: true, message: 'Please search and select a patient'},
                ]}
              >
                <AutoComplete
                  options={patientOptions}
                  onSearch={(value) => searchPatient(value)}
                  onSelect={onPatientSelect}
                  placeholder='Type name, MRN or phone'
                  notFoundContent={
                    searchingPatient ? 'Searching...' : 'No patients found'
                  }
                >
                  <Input
                    prefix={<UserOutlined />}
                    placeholder='Type at least 2 characters...'
                  />
                </AutoComplete>
              </Form.Item>
              <Form.Item name='patient_id' hidden>
                <Input />
              </Form.Item>
              {selectedPatient && (
                <div className='alert alert-info'>
                  <strong>{selectedPatient.first_name} {selectedPatient.last_name}</strong>
                  <br />
                  MRN: {selectedPatient.mrn || selectedPatient.patient_code} · 
                  Phone: {selectedPatient.primary_phone || '-'} · 
                  DOB: {selectedPatient.dob || '-'}
                </div>
              )}
            </>
          ) : (
            <Row gutter={16}>
              <Col span={8}>
                <Form.Item
                  label='First Name'
                  name='first_name'
                  rules={[{required: true, message: 'Required'}]}
                >
                  <Input />
                </Form.Item>
              </Col>
              <Col span={8}>
                <Form.Item
                  label='Last Name'
                  name='last_name'
                >
                  <Input />
                </Form.Item>
              </Col>
              <Col span={8}>
                <Form.Item
                  label='Phone'
                  name='primary_phone'
                  rules={[{required: true, message: 'Required'}]}
                >
                  <Input prefix={<PhoneOutlined />} />
                </Form.Item>
              </Col>
              <Col span={12}>
                <Form.Item label='Gender' name='gender'>
                  <Select allowClear placeholder='Select'>
                    <Option value='male'>Male</Option>
                    <Option value='female'>Female</Option>
                    <Option value='other'>Other</Option>
                  </Select>
                </Form.Item>
              </Col>
              <Col span={12}>
                <Form.Item label='DOB' name='dob'>
                  <Input type='date' />
                </Form.Item>
              </Col>
            </Row>
          )}
        </Card>

        <Card title='Clinical Assignment' className='mb-3'>
          <Row gutter={16}>
            <Col span={8}>
              <Form.Item
                label='Doctor'
                name='doctor_id'
                rules={[{required: true, message: 'Doctor is required'}]}
              >
                <Input
                  type='number'
                  placeholder='Doctor ID'
                  prefix={<UserOutlined />}
                />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label='Department' name='department_id'>
                <Input type='number' placeholder='Department ID' />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label='Chamber' name='chamber_id'>
                <Input type='number' placeholder='Chamber ID' />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={8}>
              <Form.Item
                label='Appointment Date'
                name='appointment_date'
                rules={[{required: true, message: 'Date is required'}]}
              >
                <Input type='date' prefix={<CalendarOutlined />} />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label='Consultation Mode' name='consultation_mode'>
                <Select>
                  <Option value='in_person'>In Person</Option>
                  <Option value='telemedicine'>Telemedicine</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label='Consultation Fee' name='consultation_fee'>
                <InputNumber
                  min={0}
                  style={{width: '100%'}}
                  prefix='৳'
                />
              </Form.Item>
            </Col>
          </Row>

          {walkInType === 'immediate' ? (
            <Row gutter={16}>
              <Col span={8}>
                <Form.Item
                  label='Start Time'
                  name='start_time'
                  rules={[{required: true, message: 'Start time is required'}]}
                >
                  <Input type='time' />
                </Form.Item>
              </Col>
              <Col span={8}>
                <Form.Item
                  label='End Time'
                  name='end_time'
                  rules={[{required: true, message: 'End time is required'}]}
                >
                  <Input type='time' />
                </Form.Item>
              </Col>
              <Col span={8}>
                <Form.Item
                  label='Duration (min)'
                  name='duration_minutes'
                  initialValue={15}
                >
                  <InputNumber min={5} max={240} step={5} style={{width: '100%'}} />
                </Form.Item>
              </Col>
            </Row>
          ) : (
            <Form.Item label='Available Slots' name='slot'>
              <Select
                loading={loadingSlots}
                placeholder='Select an available time slot'
                notFoundContent={
                  loadingSlots
                    ? 'Loading slots...'
                    : 'No slots available for this selection'
                }
              >
                {slots.map((s: any) => (
                  <Option
                    key={`${s.start_time}-${s.end_time}`}
                    value={`${s.start_time}|${s.end_time}|${s.duration_minutes}`}
                  >
                    {s.start_time} - {s.end_time} ({s.duration_minutes} min)
                    {s.available_count != null ? ` · ${s.available_count} left` : ''}
                  </Option>
                ))}
              </Select>
            </Form.Item>
          )}
        </Card>

        <Card title='Notes' className='mb-3'>
          <Form.Item label='Reason for Visit' name='reason'>
            <Input placeholder='e.g., Follow-up, New complaint' />
          </Form.Item>
          <Form.Item label='Notes' name='notes'>
            <TextArea rows={3} placeholder='Optional clinical notes' />
          </Form.Item>
          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                label='Send SMS Reminder'
                name='send_sms_reminder'
                valuePropName='checked'
              >
                <Switch />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                label='Send Email Reminder'
                name='send_email_reminder'
                valuePropName='checked'
              >
                <Switch />
              </Form.Item>
            </Col>
          </Row>
        </Card>

        <Divider />

        <Space>
          <Button
            type='primary'
            htmlType='submit'
            loading={submitting}
            size='large'
          >
            {walkInType === 'immediate'
              ? 'Check In & Add to Queue'
              : 'Schedule Walk-in'}
          </Button>
          <Button onClick={() => form.resetFields()} size='large'>
            Reset
          </Button>
          <Button onClick={() => navigate('/appointment/list')} size='large'>
            Cancel
          </Button>
        </Space>
      </Form>
    </div>
  )
}

export default WalkInController

import React, {FC, useEffect, useState} from 'react'
import {Button, Card, DatePicker, Form, Input, InputNumber, Modal, Select, Spin, Tag, Typography} from 'antd'
import dayjs from 'dayjs'
import {PatientPortalApi, PatientPortalPaymentApi} from 'src/app/api'
import {useEmployeeList} from 'src/app/hooks/lists/useEmployeeList'
import {Message} from 'src/app/utils'

const {Title} = Typography
const {Option} = Select

const PatientAppointmentsController: FC = () => {
  const [loading, setLoading] = useState<boolean>(true)
  const [rows, setRows] = useState<any[]>([])
  const [bookModalOpen, setBookModalOpen] = useState<boolean>(false)
  const [submitting, setSubmitting] = useState<boolean>(false)
  const [slots, setSlots] = useState<any[]>([])
  const [loadingSlots, setLoadingSlots] = useState<boolean>(false)
  const [cancellingId, setCancellingId] = useState<number | null>(null)
  const [payModalOpen, setPayModalOpen] = useState(false)
  const [activeAppointment, setActiveAppointment] = useState<any>(null)
  const [transactionRef, setTransactionRef] = useState<string | null>(null)
  const [paying, setPaying] = useState(false)
  const [form] = Form.useForm()
  const [payForm] = Form.useForm()

  const {employeeList} = useEmployeeList()

  const watchedDoctorId = Form.useWatch('doctor_id', form)
  const watchedDate = Form.useWatch('appointment_date', form)

  const loadAppointments = () => {
    setLoading(true)
    PatientPortalApi.getAppointments()
      .then((res: any) => {
        setRows(res?.data || [])
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }

  useEffect(() => {
    loadAppointments()
  }, [])

  useEffect(() => {
    if (watchedDoctorId && watchedDate) {
      setLoadingSlots(true)
      PatientPortalApi.getAvailableSlots({
        doctor_id: watchedDoctorId,
        date: dayjs(watchedDate).format('YYYY-MM-DD'),
      })
        .then((res: any) => setSlots(res?.data || []))
        .catch(() => setSlots([]))
        .finally(() => setLoadingSlots(false))
    } else {
      setSlots([])
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [watchedDoctorId, watchedDate])

  const handleOpenBookModal = () => {
    form.resetFields()
    setSlots([])
    setBookModalOpen(true)
  }

  const handleBook = (values: any) => {
    setSubmitting(true)
    const payload: any = {
      doctor_id: values.doctor_id,
      appointment_date: dayjs(values.appointment_date).format('YYYY-MM-DD'),
      appointment_time: values.appointment_time,
      reason: values.reason,
    }
    if (values.appointment_slot_id) {
      payload.appointment_slot_id = values.appointment_slot_id
    }
    PatientPortalApi.bookAppointment(payload)
      .then(() => {
        Message.success('Appointment booked successfully.')
        setSubmitting(false)
        setBookModalOpen(false)
        loadAppointments()
      })
      .catch((err: any) => {
        const msg = typeof err?.data === 'string' ? err.data : 'Could not book the appointment. Please try again.'
        Message.error(msg)
        setSubmitting(false)
      })
  }

  const handleCancel = (id: number) => {
    Modal.confirm({
      title: 'Cancel this appointment?',
      content: 'Appointments can only be cancelled up to 2 hours in advance.',
      okText: 'Yes, Cancel',
      okButtonProps: {danger: true},
      onOk: () => {
        setCancellingId(id)
        PatientPortalApi.cancelAppointment(id)
          .then(() => {
            Message.success('Appointment cancelled.')
            setCancellingId(null)
            loadAppointments()
          })
          .catch((err: any) => {
            const msg = typeof err?.data === 'string' ? err.data : 'Could not cancel the appointment.'
            Message.error(msg)
            setCancellingId(null)
          })
      },
    })
  }

  const openPay = (appointment: any) => {
    setActiveAppointment(appointment)
    payForm.resetFields()
    payForm.setFieldsValue({amount: Number(appointment.consultation_fee) || undefined})
    setTransactionRef(null)
    setPayModalOpen(true)
  }

  const handleInitiatePay = async () => {
    try {
      const values = await payForm.validateFields()
      setPaying(true)
      const res: any = await PatientPortalPaymentApi.initiate({
        payable_type: 'appointment',
        payable_id: activeAppointment.id,
        amount: values.amount,
      })
      setTransactionRef(res?.data?.transaction_ref)
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error(typeof err?.data === 'string' ? err.data : 'Could not start payment.')
    } finally {
      setPaying(false)
    }
  }

  const handleConfirmPay = (outcome: 'success' | 'failure') => {
    if (!transactionRef) return
    setPaying(true)
    PatientPortalPaymentApi.confirm(transactionRef, outcome)
      .then(() => {
        Message.success(outcome === 'success' ? 'Payment successful — appointment confirmed.' : 'Payment marked as failed.')
        setPayModalOpen(false)
        loadAppointments()
      })
      .catch(() => Message.error('Could not confirm payment.'))
      .finally(() => setPaying(false))
  }

  return (
    <Spin spinning={loading}>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={3} className='mb-0'>
          Appointments
        </Title>
        <Button type='primary' onClick={handleOpenBookModal}>
          Book New Appointment
        </Button>
      </div>

      <Card>
        <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
          <thead>
            <tr>
              <th>Serial No.</th>
              <th>Appointment No.</th>
              <th>Doctor</th>
              <th>Date</th>
              <th>Time</th>
              <th>Status</th>
              <th>Check-in Code</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 && (
              <tr>
                <td colSpan={8} align='center'>
                  No appointments found.
                </td>
              </tr>
            )}
            {rows.map((row: any, index: number) => (
              <tr key={row.id}>
                <td align='center'>{index + 1}</td>
                <td>{row.appointment_no}</td>
                <td>{row.doctor_name || '-'}</td>
                <td>{typeof row.appointment_date === 'string' ? row.appointment_date.slice(0, 10) : row.appointment_date}</td>
                <td>{row.appointment_time}</td>
                <td>
                  <Tag color={row.status === 'cancelled' ? 'error' : row.status === 'completed' ? 'success' : 'processing'}>
                    {row.status_label || row.status}
                  </Tag>
                </td>
                <td className='fs-8 text-muted' style={{maxWidth: 160, wordBreak: 'break-all'}}>
                  {row.status !== 'cancelled' && row.status !== 'completed' ? row.uuid : '-'}
                </td>
                <td>
                  {row.status === 'pending' && (
                    <Button size='small' type='primary' className='me-2' onClick={() => openPay(row)}>
                      Pay Now
                    </Button>
                  )}
                  {row.status !== 'cancelled' && row.status !== 'completed' && (
                    <Button
                      size='small'
                      danger
                      loading={cancellingId === row.id}
                      onClick={() => handleCancel(row.id)}
                    >
                      Cancel
                    </Button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>

      <Modal
        title='Book New Appointment'
        open={bookModalOpen}
        onCancel={() => setBookModalOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={submitting}
        okText='Book Appointment'
      >
        <Form form={form} layout='vertical' onFinish={handleBook}>
          <Form.Item name='doctor_id' label='Doctor' rules={[{required: true, message: 'Please select a doctor'}]}>
            <Select placeholder='Select doctor' showSearch optionFilterProp='children'>
              {employeeList.map((emp: any) => (
                <Option key={emp.id} value={emp.id}>
                  {emp.name_en}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item
            name='appointment_date'
            label='Date'
            rules={[{required: true, message: 'Please select a date'}]}
          >
            <DatePicker className='w-100' disabledDate={(d) => d && d.isBefore(dayjs().startOf('day'))} />
          </Form.Item>
          <Form.Item name='appointment_slot_id' label='Available Slot (optional)'>
            <Select
              placeholder={loadingSlots ? 'Loading slots...' : 'Select a slot, or enter time manually below'}
              loading={loadingSlots}
              allowClear
              onChange={(value) => {
                const slot = slots.find((s: any) => s.id === value)
                if (slot) {
                  form.setFieldsValue({appointment_time: slot.start_time?.slice(0, 5)})
                }
              }}
            >
              {slots.map((slot: any) => (
                <Option key={slot.id} value={slot.id} disabled={!slot.available}>
                  {slot.start_time?.slice(0, 5)} - {slot.end_time?.slice(0, 5)} ({slot.available} available)
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item
            name='appointment_time'
            label='Time (HH:mm)'
            rules={[
              {required: true, message: 'Please enter or select a time'},
              {pattern: /^\d{2}:\d{2}$/, message: 'Use HH:mm format, e.g. 14:30'},
            ]}
          >
            <Input placeholder='e.g. 14:30' />
          </Form.Item>
          <Form.Item name='reason' label='Reason for Visit'>
            <Input.TextArea rows={3} placeholder='Briefly describe the reason for your visit' />
          </Form.Item>
        </Form>
      </Modal>

      <Modal title='Pay for Appointment' open={payModalOpen} onCancel={() => setPayModalOpen(false)} footer={null}>
        {!transactionRef ? (
          <Form form={payForm} layout='vertical' onFinish={handleInitiatePay}>
            <Form.Item name='amount' label='Amount' rules={[{required: true, message: 'Enter the payment amount'}]}>
              <InputNumber className='w-100' min={1} />
            </Form.Item>
            <Button type='primary' block htmlType='submit' loading={paying}>
              Proceed to Payment
            </Button>
          </Form>
        ) : (
          <div>
            <p className='text-muted'>
              Transaction <Tag>{transactionRef}</Tag> initiated. This is a demo payment gateway — choose an outcome to
              simulate the checkout result.
            </p>
            <div className='d-flex mt-4' style={{gap: 8}}>
              <Button type='primary' block loading={paying} onClick={() => handleConfirmPay('success')}>
                Simulate Successful Payment
              </Button>
              <Button danger block loading={paying} onClick={() => handleConfirmPay('failure')}>
                Simulate Failed Payment
              </Button>
            </div>
          </div>
        )}
      </Modal>
    </Spin>
  )
}

export default PatientAppointmentsController

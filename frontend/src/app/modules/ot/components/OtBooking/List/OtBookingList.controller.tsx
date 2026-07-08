import React, {FC, useEffect, useState} from 'react'
import {Button, Card, DatePicker, Form, Input, Modal, Select, Tag, Typography} from 'antd'
import dayjs from 'dayjs'
import {useNavigate} from 'react-router-dom'
import {OtBookingApi, PatientApi, TheatreApi} from 'src/app/api'
import {useEmployeeList} from 'src/app/hooks/lists/useEmployeeList'
import {Message} from 'src/app/utils'

const {Title} = Typography
const {Option} = Select

const statusColor: Record<string, string> = {
  scheduled: 'processing',
  in_progress: 'warning',
  completed: 'success',
  cancelled: 'error',
}

const OtBookingListController: FC = () => {
  const navigate = useNavigate()
  const {employeeList} = useEmployeeList()
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [theatres, setTheatres] = useState<any[]>([])
  const [bookModalOpen, setBookModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [patientSearch, setPatientSearch] = useState('')
  const [patientOptions, setPatientOptions] = useState<any[]>([])
  const [form] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    OtBookingApi.list()
      .then((res: any) => setRows(res?.data?.results ?? res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    TheatreApi.list()
      .then((res: any) => setTheatres(res?.data?.results ?? res?.data ?? []))
      .catch(() => setTheatres([]))
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

  const handleBook = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await OtBookingApi.create({
        ...values,
        scheduled_date: dayjs(values.scheduled_date).format('YYYY-MM-DD'),
      })
      Message.success('Surgery booked successfully.')
      setBookModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error(typeof err?.data === 'string' ? err.data : 'Failed to book surgery.')
    } finally {
      setSaving(false)
    }
  }

  const handleCancel = (id: any) => {
    Modal.confirm({
      title: 'Cancel this OT booking?',
      okText: 'Yes, Cancel',
      okButtonProps: {danger: true},
      onOk: () => {
        OtBookingApi.cancel(id, {cancellation_reason: 'Cancelled by staff'})
          .then(() => {
            Message.success('Booking cancelled.')
            loadData()
          })
          .catch(() => Message.error('Failed to cancel booking.'))
      },
    })
  }

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={3} className='mb-0'>
          OT Bookings
        </Title>
        <Button
          type='primary'
          onClick={() => {
            form.resetFields()
            setBookModalOpen(true)
          }}
        >
          Book Surgery
        </Button>
      </div>

      <Card loading={loading}>
        <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
          <thead>
            <tr>
              <th>Booking No.</th>
              <th>Patient</th>
              <th>Surgery</th>
              <th>Theatre</th>
              <th>Surgeon</th>
              <th>Date</th>
              <th>Time</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 && (
              <tr>
                <td colSpan={9} align='center'>
                  No OT bookings found.
                </td>
              </tr>
            )}
            {rows.map((row: any) => (
              <tr key={row.id}>
                <td>{row.booking_no}</td>
                <td>{row.patient_name || row.patient?.first_name || '-'}</td>
                <td>{row.surgery_name}</td>
                <td>{row.theatre?.name || '-'}</td>
                <td>{row.surgeon?.name_en || '-'}</td>
                <td>{typeof row.scheduled_date === 'string' ? row.scheduled_date.slice(0, 10) : row.scheduled_date}</td>
                <td>{row.scheduled_start_time?.slice(0, 5)} - {row.scheduled_end_time?.slice(0, 5)}</td>
                <td>
                  <Tag color={statusColor[row.booking_status] || 'default'}>{row.booking_status_label || row.booking_status}</Tag>
                </td>
                <td>
                  <Button size='small' className='me-2' onClick={() => navigate(`/admin/ot/booking/${row.id}`)}>
                    View
                  </Button>
                  {row.booking_status === 'scheduled' && (
                    <Button size='small' danger onClick={() => handleCancel(row.id)}>
                      Cancel
                    </Button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>

      <Modal title='Book Surgery' open={bookModalOpen} onCancel={() => setBookModalOpen(false)} onOk={handleBook} confirmLoading={saving} width={600}>
        <Form form={form} layout='vertical'>
          <Form.Item name='patient_id' label='Patient' rules={[{required: true}]}>
            <Select
              showSearch
              filterOption={false}
              placeholder='Search patient by name/phone/MRN'
              onSearch={setPatientSearch}
              notFoundContent={null}
            >
              {patientOptions.map((p: any) => (
                <Option key={p.id} value={p.id}>
                  {p.full_name || `${p.first_name} ${p.last_name}`} ({p.mrn})
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='theatre_id' label='Theatre' rules={[{required: true}]}>
            <Select>
              {theatres.map((t: any) => (
                <Option key={t.id} value={t.id}>
                  {t.name}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='surgeon_id' label='Surgeon' rules={[{required: true}]}>
            <Select showSearch optionFilterProp='children'>
              {employeeList.map((emp: any) => (
                <Option key={emp.id} value={emp.id}>
                  {emp.name_en}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='anaesthetist_id' label='Anaesthetist'>
            <Select showSearch optionFilterProp='children' allowClear>
              {employeeList.map((emp: any) => (
                <Option key={emp.id} value={emp.id}>
                  {emp.name_en}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='surgery_name' label='Surgery Name' rules={[{required: true}]}>
            <Input placeholder='e.g. Appendectomy' />
          </Form.Item>
          <Form.Item name='surgery_type' label='Surgery Type'>
            <Select
              options={[
                {value: 'elective', label: 'Elective'},
                {value: 'emergency', label: 'Emergency'},
              ]}
            />
          </Form.Item>
          <Form.Item name='scheduled_date' label='Date' rules={[{required: true}]}>
            <DatePicker className='w-100' disabledDate={(d) => d && d.isBefore(dayjs().startOf('day'))} />
          </Form.Item>
          <Form.Item name='scheduled_start_time' label='Start Time (HH:mm)' rules={[{required: true}]}>
            <Input placeholder='e.g. 09:00' />
          </Form.Item>
          <Form.Item name='scheduled_end_time' label='End Time (HH:mm)' rules={[{required: true}]}>
            <Input placeholder='e.g. 11:00' />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default OtBookingListController

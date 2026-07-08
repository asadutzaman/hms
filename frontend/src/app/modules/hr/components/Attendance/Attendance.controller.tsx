import React, {FC, useEffect, useState} from 'react'
import {Button, Card, DatePicker, Form, Input, Modal, Select, Tag, Typography} from 'antd'
import dayjs from 'dayjs'
import {AttendanceRecordApi} from 'src/app/api'
import {useEmployeeList} from 'src/app/hooks/lists/useEmployeeList'
import {Message} from 'src/app/utils'

const {Title} = Typography
const {Option} = Select

const AttendanceController: FC = () => {
  const {employeeList} = useEmployeeList()
  const [employeeId, setEmployeeId] = useState<any>(null)
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [checking, setChecking] = useState(false)
  const [manualModalOpen, setManualModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = (empId: any) => {
    if (!empId) {
      setRows([])
      return
    }
    setLoading(true)
    AttendanceRecordApi.forEmployee(empId, {
      start_date: dayjs().startOf('month').format('YYYY-MM-DD'),
      end_date: dayjs().format('YYYY-MM-DD'),
    })
      .then((res: any) => setRows(res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData(employeeId)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [employeeId])

  const handleCheckIn = () => {
    if (!employeeId) return Message.error('Select an employee first.')
    setChecking(true)
    AttendanceRecordApi.checkIn({employee_id: employeeId})
      .then(() => {
        Message.success('Checked in.')
        loadData(employeeId)
      })
      .catch((err: any) => Message.error(typeof err?.data === 'string' ? err.data : 'Check-in failed.'))
      .finally(() => setChecking(false))
  }

  const handleCheckOut = () => {
    if (!employeeId) return Message.error('Select an employee first.')
    setChecking(true)
    AttendanceRecordApi.checkOut({employee_id: employeeId})
      .then(() => {
        Message.success('Checked out.')
        loadData(employeeId)
      })
      .catch((err: any) => Message.error(typeof err?.data === 'string' ? err.data : 'Check-out failed.'))
      .finally(() => setChecking(false))
  }

  const handleManualEntry = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await AttendanceRecordApi.manualEntry({
        ...values,
        attendance_date: dayjs(values.attendance_date).format('YYYY-MM-DD'),
        check_in_time: values.check_in_time ? dayjs(values.check_in_time).format('YYYY-MM-DD HH:mm:ss') : null,
        check_out_time: values.check_out_time ? dayjs(values.check_out_time).format('YYYY-MM-DD HH:mm:ss') : null,
      })
      Message.success('Attendance record saved.')
      setManualModalOpen(false)
      loadData(employeeId)
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to save attendance record.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={3} className='mb-0'>
          Attendance
        </Title>
        <div className='d-flex' style={{gap: 8}}>
          <Button
            onClick={() => {
              form.resetFields()
              form.setFieldsValue({employee_id: employeeId, attendance_date: dayjs()})
              setManualModalOpen(true)
            }}
          >
            Manual Entry
          </Button>
          <Button onClick={handleCheckIn} loading={checking}>
            Check In
          </Button>
          <Button onClick={handleCheckOut} loading={checking}>
            Check Out
          </Button>
        </div>
      </div>

      <Card className='mb-4'>
        <label className='form-label'>Employee</label>
        <Select
          className='w-100'
          placeholder='Select employee'
          showSearch
          optionFilterProp='children'
          value={employeeId}
          onChange={setEmployeeId}
        >
          {employeeList.map((emp: any) => (
            <Option key={emp.id} value={emp.id}>
              {emp.name_en}
            </Option>
          ))}
        </Select>
      </Card>

      <Card loading={loading}>
        <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
          <thead>
            <tr>
              <th>Date</th>
              <th>Check In</th>
              <th>Check Out</th>
              <th>Work Hours</th>
              <th>Source</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 && (
              <tr>
                <td colSpan={5} align='center'>
                  {employeeId ? 'No attendance records found.' : 'Select an employee to view attendance.'}
                </td>
              </tr>
            )}
            {rows.map((row: any) => (
              <tr key={row.id}>
                <td>{row.attendance_date}</td>
                <td>{row.check_in_time || '-'}</td>
                <td>{row.check_out_time || '-'}</td>
                <td>{row.work_hours ?? '-'}</td>
                <td>
                  <Tag color={row.source === 'biometric' ? 'blue' : 'default'}>{row.source}</Tag>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>

      <Modal
        title='Manual Attendance Entry'
        open={manualModalOpen}
        onCancel={() => setManualModalOpen(false)}
        onOk={handleManualEntry}
        confirmLoading={saving}
      >
        <Form form={form} layout='vertical'>
          <Form.Item name='employee_id' label='Employee' rules={[{required: true}]}>
            <Select showSearch optionFilterProp='children'>
              {employeeList.map((emp: any) => (
                <Option key={emp.id} value={emp.id}>
                  {emp.name_en}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='attendance_date' label='Date' rules={[{required: true}]}>
            <DatePicker className='w-100' />
          </Form.Item>
          <Form.Item name='check_in_time' label='Check In Time'>
            <DatePicker showTime className='w-100' />
          </Form.Item>
          <Form.Item name='check_out_time' label='Check Out Time'>
            <DatePicker showTime className='w-100' />
          </Form.Item>
          <Form.Item name='remarks' label='Remarks'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default AttendanceController

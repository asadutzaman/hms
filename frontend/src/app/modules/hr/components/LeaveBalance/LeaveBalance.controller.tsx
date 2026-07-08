import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Form, InputNumber, Modal, Select, Typography} from 'antd'
import {LeaveBalanceApi, LeaveTypeApi} from 'src/app/api'
import {useEmployeeList} from 'src/app/hooks/lists/useEmployeeList'
import {Message} from 'src/app/utils'

const {Title} = Typography
const {Option} = Select

const LeaveBalanceController: FC = () => {
  const {employeeList} = useEmployeeList()
  const [employeeId, setEmployeeId] = useState<any>(null)
  const [loading, setLoading] = useState(false)
  const [balances, setBalances] = useState<any[]>([])
  const [leaveTypes, setLeaveTypes] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = (empId: any) => {
    if (!empId) {
      setBalances([])
      return
    }
    setLoading(true)
    LeaveBalanceApi.forEmployee(empId, {year: new Date().getFullYear()})
      .then((res: any) => setBalances(res?.data ?? []))
      .catch(() => setBalances([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData(employeeId)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [employeeId])

  useEffect(() => {
    LeaveTypeApi.list()
      .then((res: any) => setLeaveTypes(res?.data?.results ?? res?.data ?? []))
      .catch(() => setLeaveTypes([]))
  }, [])

  const handleAllocate = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await LeaveBalanceApi.allocate({...values, year: new Date().getFullYear()})
      Message.success('Balance allocated.')
      setModalOpen(false)
      loadData(employeeId)
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to allocate balance.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={3} className='mb-0'>
          Leave Balance
        </Title>
        <Button
          type='primary'
          onClick={() => {
            form.resetFields()
            form.setFieldsValue({employee_id: employeeId})
            setModalOpen(true)
          }}
        >
          Allocate Balance
        </Button>
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
              <th>Leave Type</th>
              <th>Allocated</th>
              <th>Used</th>
              <th>Balance</th>
            </tr>
          </thead>
          <tbody>
            {balances.length === 0 && (
              <tr>
                <td colSpan={4} align='center'>
                  {employeeId ? 'No balance data found.' : 'Select an employee to view leave balance.'}
                </td>
              </tr>
            )}
            {balances.map((b: any, i: number) => (
              <tr key={i}>
                <td>{b.leave_type_name}</td>
                <td>{b.allocated_days}</td>
                <td>{b.used_days}</td>
                <td>{b.balance}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>

      <Modal title='Allocate Leave Balance' open={modalOpen} onCancel={() => setModalOpen(false)} onOk={handleAllocate} confirmLoading={saving}>
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
          <Form.Item name='leave_type_id' label='Leave Type' rules={[{required: true}]}>
            <Select>
              {leaveTypes.map((lt: any) => (
                <Option key={lt.id} value={lt.id}>
                  {lt.name}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='allocated_days' label='Allocated Days' rules={[{required: true}]}>
            <InputNumber className='w-100' min={0} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default LeaveBalanceController

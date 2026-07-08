import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Form, Input, InputNumber, Modal, Switch, Tabs, Typography} from 'antd'
import {ShiftApi, LeaveTypeApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography

const ShiftMaster: FC = () => {
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    ShiftApi.list()
      .then((res: any) => setRows(res?.data?.results ?? res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const handleSave = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await ShiftApi.create(values)
      Message.success('Shift created')
      setModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to create shift')
    } finally {
      setSaving(false)
    }
  }

  return (
    <Card
      loading={loading}
      title='Shifts'
      extra={
        <Button
          type='primary'
          onClick={() => {
            form.resetFields()
            setModalOpen(true)
          }}
        >
          Add Shift
        </Button>
      }
    >
      <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
        <thead>
          <tr>
            <th>Name</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          {rows.length === 0 && (
            <tr>
              <td colSpan={4} align='center'>
                No shifts found.
              </td>
            </tr>
          )}
          {rows.map((row: any) => (
            <tr key={row.id}>
              <td>{row.name}</td>
              <td>{row.start_time}</td>
              <td>{row.end_time}</td>
              <td>{row.description || '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>

      <Modal title='Add Shift' open={modalOpen} onCancel={() => setModalOpen(false)} onOk={handleSave} confirmLoading={saving}>
        <Form form={form} layout='vertical'>
          <Form.Item name='name' label='Name' rules={[{required: true}]}>
            <Input placeholder='e.g. Morning Shift' />
          </Form.Item>
          <Form.Item name='start_time' label='Start Time' rules={[{required: true}]}>
            <Input type='time' />
          </Form.Item>
          <Form.Item name='end_time' label='End Time' rules={[{required: true}]}>
            <Input type='time' />
          </Form.Item>
          <Form.Item name='description' label='Description'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </Card>
  )
}

const LeaveTypeMaster: FC = () => {
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    LeaveTypeApi.list()
      .then((res: any) => setRows(res?.data?.results ?? res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const handleSave = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await LeaveTypeApi.create(values)
      Message.success('Leave type created')
      setModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to create leave type')
    } finally {
      setSaving(false)
    }
  }

  return (
    <Card
      loading={loading}
      title='Leave Types'
      extra={
        <Button
          type='primary'
          onClick={() => {
            form.resetFields()
            form.setFieldsValue({is_paid: true})
            setModalOpen(true)
          }}
        >
          Add Leave Type
        </Button>
      }
    >
      <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
        <thead>
          <tr>
            <th>Name</th>
            <th>Max Days / Year</th>
            <th>Paid</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          {rows.length === 0 && (
            <tr>
              <td colSpan={4} align='center'>
                No leave types found.
              </td>
            </tr>
          )}
          {rows.map((row: any) => (
            <tr key={row.id}>
              <td>{row.name}</td>
              <td>{row.max_days_per_year}</td>
              <td>{row.is_paid ? 'Yes' : 'No'}</td>
              <td>{row.description || '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>

      <Modal title='Add Leave Type' open={modalOpen} onCancel={() => setModalOpen(false)} onOk={handleSave} confirmLoading={saving}>
        <Form form={form} layout='vertical'>
          <Form.Item name='name' label='Name' rules={[{required: true}]}>
            <Input placeholder='e.g. Annual Leave' />
          </Form.Item>
          <Form.Item name='max_days_per_year' label='Max Days / Year' rules={[{required: true}]}>
            <InputNumber className='w-100' min={0} />
          </Form.Item>
          <Form.Item name='is_paid' label='Paid Leave' valuePropName='checked'>
            <Switch />
          </Form.Item>
          <Form.Item name='description' label='Description'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </Card>
  )
}

const HrMastersController: FC = () => {
  return (
    <div className='p-6'>
      <Title level={3}>HR Masters</Title>
      <Tabs
        items={[
          {key: 'shifts', label: 'Shifts', children: <ShiftMaster />},
          {key: 'leave-types', label: 'Leave Types', children: <LeaveTypeMaster />},
        ]}
      />
    </div>
  )
}

export default HrMastersController

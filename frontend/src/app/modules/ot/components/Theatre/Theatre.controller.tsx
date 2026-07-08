import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Form, Input, Modal, Select, Typography} from 'antd'
import {TheatreApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography

const TheatreController: FC = () => {
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    TheatreApi.list()
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
      await TheatreApi.create(values)
      Message.success('Theatre created')
      setModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to create theatre')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={3} className='mb-0'>
          Theatres
        </Title>
        <Button
          type='primary'
          onClick={() => {
            form.resetFields()
            form.setFieldsValue({theatre_type: 'general'})
            setModalOpen(true)
          }}
        >
          Add Theatre
        </Button>
      </div>

      <Card loading={loading}>
        <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
          <thead>
            <tr>
              <th>Name</th>
              <th>Floor</th>
              <th>Type</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 && (
              <tr>
                <td colSpan={4} align='center'>
                  No theatres found.
                </td>
              </tr>
            )}
            {rows.map((row: any) => (
              <tr key={row.id}>
                <td>{row.name}</td>
                <td>{row.floor || '-'}</td>
                <td className='text-capitalize'>{row.theatre_type}</td>
                <td>{row.description || '-'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>

      <Modal title='Add Theatre' open={modalOpen} onCancel={() => setModalOpen(false)} onOk={handleSave} confirmLoading={saving}>
        <Form form={form} layout='vertical'>
          <Form.Item name='name' label='Name' rules={[{required: true}]}>
            <Input placeholder='e.g. OT-1 Main Theatre' />
          </Form.Item>
          <Form.Item name='floor' label='Floor'>
            <Input placeholder='e.g. 2nd Floor' />
          </Form.Item>
          <Form.Item name='theatre_type' label='Type'>
            <Select
              options={[
                {value: 'general', label: 'General'},
                {value: 'cardiac', label: 'Cardiac'},
                {value: 'orthopedic', label: 'Orthopedic'},
                {value: 'minor', label: 'Minor Procedure'},
              ]}
            />
          </Form.Item>
          <Form.Item name='description' label='Description'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default TheatreController

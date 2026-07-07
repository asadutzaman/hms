import React, {FC, useEffect, useState} from 'react'
import {Button, Empty, Form, InputNumber, Input, Modal, Popconfirm, Skeleton, Table} from 'antd'
import {DeleteOutlined, EditOutlined, PlusOutlined} from '@ant-design/icons'
import {InsuranceSchemeApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {TextArea} = Input

interface InsuranceSchemePanelProps {
  insuranceCompanyId: any
}

const InsuranceSchemePanel: FC<InsuranceSchemePanelProps> = ({insuranceCompanyId}) => {
  const [loading, setLoading] = useState(false)
  const [schemes, setSchemes] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [editingId, setEditingId] = useState<any>(null)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = () => {
    if (!insuranceCompanyId) return
    setLoading(true)
    InsuranceSchemeApi.byCompany(insuranceCompanyId)
      .then((res: any) => setSchemes(res?.data?.data ?? res?.data ?? []))
      .catch(() => setSchemes([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [insuranceCompanyId])

  const handleAdd = () => {
    setEditingId(null)
    form.resetFields()
    form.setFieldsValue({coverage_percent: 100})
    setModalOpen(true)
  }

  const handleEdit = (row: any) => {
    setEditingId(row.id)
    form.setFieldsValue({
      name: row.name,
      coverage_percent: row.coverage_percent,
      max_limit: row.max_limit,
      covered_services: row.covered_services,
    })
    setModalOpen(true)
  }

  const handleSave = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      if (editingId) {
        await InsuranceSchemeApi.update(editingId, values)
        Message.success('Scheme updated')
      } else {
        await InsuranceSchemeApi.create({...values, insurance_company_id: insuranceCompanyId})
        Message.success('Scheme added')
      }
      setModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to save scheme')
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = (id: any) => {
    InsuranceSchemeApi.delete(id)
      .then(() => {
        Message.success('Scheme removed')
        loadData()
      })
      .catch(() => Message.error('Failed to remove scheme'))
  }

  const columns: any[] = [
    {title: 'Scheme Name', dataIndex: 'name', key: 'name'},
    {title: 'Coverage %', dataIndex: 'coverage_percent', key: 'coverage_percent', render: (v: any) => `${v}%`},
    {title: 'Max Limit', dataIndex: 'max_limit', key: 'max_limit', render: (v: any) => v ?? '-'},
    {title: 'Covered Services', dataIndex: 'covered_services', key: 'covered_services', render: (v: any) => v || '-'},
    {
      title: 'Action',
      key: 'action',
      render: (_: any, row: any) => (
        <>
          <Button size='small' className='me-2' icon={<EditOutlined />} onClick={() => handleEdit(row)} />
          <Popconfirm title='Remove this scheme?' onConfirm={() => handleDelete(row.id)}>
            <Button size='small' danger icon={<DeleteOutlined />} />
          </Popconfirm>
        </>
      ),
    },
  ]

  return (
    <div>
      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>Schemes</h5>
        <Button size='small' type='primary' icon={<PlusOutlined />} onClick={handleAdd}>
          Add Scheme
        </Button>
      </div>

      {loading ? (
        <Skeleton active paragraph={{rows: 3}} />
      ) : schemes.length ? (
        <Table rowKey='id' size='small' columns={columns} dataSource={schemes} pagination={false} />
      ) : (
        <Empty description='No schemes configured yet' />
      )}

      <Modal
        title={editingId ? 'Edit Scheme' : 'Add Scheme'}
        open={modalOpen}
        onCancel={() => setModalOpen(false)}
        onOk={handleSave}
        confirmLoading={saving}
        destroyOnClose
      >
        <Form form={form} layout='vertical'>
          <Form.Item name='name' label='Scheme Name' rules={[{required: true}]}>
            <Input placeholder='e.g. Gold Health Plan' />
          </Form.Item>
          <Form.Item name='coverage_percent' label='Coverage %' rules={[{required: true}]}>
            <InputNumber min={0} max={100} style={{width: '100%'}} />
          </Form.Item>
          <Form.Item name='max_limit' label='Max Limit'>
            <InputNumber min={0} style={{width: '100%'}} />
          </Form.Item>
          <Form.Item name='covered_services' label='Covered Services'>
            <TextArea rows={3} placeholder='e.g. Consultation, Lab, Radiology, Pharmacy' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default InsuranceSchemePanel

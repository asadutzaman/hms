import React, {FC, useEffect, useState} from 'react'
import {Button, Empty, Form, Input, Modal, Popconfirm, Select, Skeleton, Table, Tag} from 'antd'
import {DeleteOutlined, PlusOutlined} from '@ant-design/icons'
import {PatientApi, PatientAllergyApi} from 'src/app/api'
import {DateTimeUtils, Message} from 'src/app/utils'

const severityColor: Record<string, string> = {
  severe: 'red',
  moderate: 'orange',
  mild: 'gold',
}

interface AllergyPanelProps {
  patientId: any
  canEdit?: boolean
}

const AllergyPanel: FC<AllergyPanelProps> = ({patientId, canEdit = true}) => {
  const [loading, setLoading] = useState(false)
  const [allergies, setAllergies] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = () => {
    if (!patientId) return
    setLoading(true)
    PatientApi.allergies(patientId)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? []
        setAllergies(Array.isArray(data) ? data : [])
      })
      .catch(() => setAllergies([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [patientId])

  const handleAdd = () => {
    form.resetFields()
    form.setFieldsValue({allergy_type: 'drug', severity: 'mild'})
    setModalOpen(true)
  }

  const handleSave = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await PatientAllergyApi.create({...values, patient_id: patientId})
      Message.success('Allergy recorded')
      setModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to record allergy')
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = (id: any) => {
    PatientAllergyApi.delete(id)
      .then(() => {
        Message.success('Allergy removed')
        loadData()
      })
      .catch(() => Message.error('Failed to remove allergy'))
  }

  const columns: any[] = [
    {title: 'Type', dataIndex: 'allergy_type', key: 'allergy_type', render: (v: string) => <span className='text-capitalize'>{v}</span>},
    {title: 'Allergen', dataIndex: 'allergen_name', key: 'allergen_name'},
    {title: 'Reaction', dataIndex: 'reaction_type', key: 'reaction_type', render: (v: string) => v || '-'},
    {
      title: 'Severity',
      dataIndex: 'severity',
      key: 'severity',
      render: (v: string) => <Tag color={severityColor[v] || 'default'}>{v?.toUpperCase()}</Tag>,
    },
    {title: 'Notes', dataIndex: 'notes', key: 'notes', render: (v: string) => v || '-'},
    {
      title: 'Recorded At',
      dataIndex: 'recorded_at',
      key: 'recorded_at',
      render: (v: string) => (v ? DateTimeUtils.formatDateTimeA(v) : '-'),
    },
  ]

  if (canEdit) {
    columns.push({
      title: 'Action',
      key: 'action',
      render: (_: any, row: any) => (
        <Popconfirm title='Remove this allergy record?' onConfirm={() => handleDelete(row.id)}>
          <Button size='small' danger icon={<DeleteOutlined />} />
        </Popconfirm>
      ),
    })
  }

  return (
    <div>
      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>Allergies</h5>
        {canEdit && (
          <Button size='small' type='primary' icon={<PlusOutlined />} onClick={handleAdd}>
            Add Allergy
          </Button>
        )}
      </div>

      {loading ? (
        <Skeleton active paragraph={{rows: 3}} />
      ) : allergies.length ? (
        <Table rowKey='id' size='small' columns={columns} dataSource={allergies} pagination={false} />
      ) : (
        <Empty description='No known allergies recorded' />
      )}

      <Modal
        title='Record Allergy'
        open={modalOpen}
        onCancel={() => setModalOpen(false)}
        onOk={handleSave}
        confirmLoading={saving}
        destroyOnClose
      >
        <Form form={form} layout='vertical'>
          <Form.Item name='allergy_type' label='Allergy Type' rules={[{required: true}]}>
            <Select
              options={[
                {value: 'drug', label: 'Drug'},
                {value: 'food', label: 'Food'},
                {value: 'environmental', label: 'Environmental'},
                {value: 'other', label: 'Other'},
              ]}
            />
          </Form.Item>
          <Form.Item name='allergen_name' label='Allergen Name' rules={[{required: true}]}>
            <Input placeholder='e.g. Penicillin' />
          </Form.Item>
          <Form.Item name='reaction_type' label='Reaction Type'>
            <Input placeholder='e.g. Rash, Anaphylaxis' />
          </Form.Item>
          <Form.Item name='severity' label='Severity' rules={[{required: true}]}>
            <Select
              options={[
                {value: 'mild', label: 'Mild'},
                {value: 'moderate', label: 'Moderate'},
                {value: 'severe', label: 'Severe'},
              ]}
            />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <Input.TextArea rows={3} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default AllergyPanel

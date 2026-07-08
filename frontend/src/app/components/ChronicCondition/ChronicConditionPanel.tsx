import React, {FC, useEffect, useState} from 'react'
import {Button, Empty, Form, Input, Modal, Select, Skeleton, Table, Tag} from 'antd'
import {PlusOutlined} from '@ant-design/icons'
import {PatientChronicConditionApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const statusColor: Record<string, string> = {
  active: 'red',
  monitoring: 'orange',
  resolved: 'green',
}

interface ChronicConditionPanelProps {
  patientId: any
  canEdit?: boolean
}

const ChronicConditionPanel: FC<ChronicConditionPanelProps> = ({patientId, canEdit = true}) => {
  const [loading, setLoading] = useState(false)
  const [conditions, setConditions] = useState<any[]>([])
  const [conditionModalOpen, setConditionModalOpen] = useState(false)
  const [readingModalOpen, setReadingModalOpen] = useState(false)
  const [activeConditionId, setActiveConditionId] = useState<any>(null)
  const [saving, setSaving] = useState(false)
  const [conditionForm] = Form.useForm()
  const [readingForm] = Form.useForm()

  const loadData = () => {
    if (!patientId) return
    setLoading(true)
    PatientChronicConditionApi.byPatient(patientId)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? []
        setConditions(Array.isArray(data) ? data : [])
      })
      .catch(() => setConditions([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [patientId])

  const handleAddCondition = () => {
    conditionForm.resetFields()
    conditionForm.setFieldsValue({condition_status: 'active'})
    setConditionModalOpen(true)
  }

  const handleSaveCondition = async () => {
    try {
      const values = await conditionForm.validateFields()
      setSaving(true)
      await PatientChronicConditionApi.create({...values, patient_id: patientId})
      Message.success('Chronic condition recorded')
      setConditionModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to record condition')
    } finally {
      setSaving(false)
    }
  }

  const handleAddReading = (conditionId: any) => {
    setActiveConditionId(conditionId)
    readingForm.resetFields()
    readingForm.setFieldsValue({reading_date: new Date().toISOString().slice(0, 10)})
    setReadingModalOpen(true)
  }

  const handleSaveReading = async () => {
    try {
      const values = await readingForm.validateFields()
      setSaving(true)
      await PatientChronicConditionApi.addReading(activeConditionId, values)
      Message.success('Reading added')
      setReadingModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to add reading')
    } finally {
      setSaving(false)
    }
  }

  const readingColumns = [
    {title: 'Date', dataIndex: 'reading_date', key: 'reading_date'},
    {title: 'Type', dataIndex: 'reading_type', key: 'reading_type', render: (v: string) => <span className='text-capitalize'>{v?.replace(/_/g, ' ')}</span>},
    {
      title: 'Value',
      key: 'value',
      render: (_: any, row: any) => `${row.reading_value}${row.unit ? ' ' + row.unit : ''}`,
    },
    {title: 'Notes', dataIndex: 'notes', key: 'notes', render: (v: string) => v || '-'},
  ]

  const columns: any[] = [
    {title: 'Condition', dataIndex: 'condition_name', key: 'condition_name'},
    {title: 'Diagnosed', dataIndex: 'diagnosed_date', key: 'diagnosed_date', render: (v: string) => v || '-'},
    {title: 'Target', dataIndex: 'target_notes', key: 'target_notes', render: (v: string) => v || '-'},
    {
      title: 'Status',
      dataIndex: 'condition_status',
      key: 'condition_status',
      render: (v: string) => <Tag color={statusColor[v] || 'default'}>{v?.toUpperCase()}</Tag>,
    },
  ]

  if (canEdit) {
    columns.push({
      title: 'Action',
      key: 'action',
      render: (_: any, row: any) => (
        <Button size='small' onClick={() => handleAddReading(row.id)}>
          Add Reading
        </Button>
      ),
    })
  }

  return (
    <div>
      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>Chronic Conditions</h5>
        {canEdit && (
          <Button size='small' type='primary' icon={<PlusOutlined />} onClick={handleAddCondition}>
            Add Condition
          </Button>
        )}
      </div>

      {loading ? (
        <Skeleton active paragraph={{rows: 3}} />
      ) : conditions.length ? (
        <Table
          rowKey='id'
          size='small'
          columns={columns}
          dataSource={conditions}
          pagination={false}
          expandable={{
            expandedRowRender: (row: any) => (
              <Table
                rowKey='id'
                size='small'
                columns={readingColumns}
                dataSource={row.readings || []}
                pagination={false}
                locale={{emptyText: 'No readings recorded yet'}}
              />
            ),
          }}
        />
      ) : (
        <Empty description='No chronic conditions recorded' />
      )}

      <Modal
        title='Record Chronic Condition'
        open={conditionModalOpen}
        onCancel={() => setConditionModalOpen(false)}
        onOk={handleSaveCondition}
        confirmLoading={saving}
        destroyOnClose
      >
        <Form form={conditionForm} layout='vertical'>
          <Form.Item name='condition_name' label='Condition Name' rules={[{required: true}]}>
            <Input placeholder='e.g. Type 2 Diabetes Mellitus' />
          </Form.Item>
          <Form.Item name='diagnosed_date' label='Diagnosed Date'>
            <Input type='date' />
          </Form.Item>
          <Form.Item name='target_notes' label='Targets / Goals'>
            <Input.TextArea rows={2} placeholder='e.g. HbA1c < 7%, fasting glucose < 130 mg/dL' />
          </Form.Item>
          <Form.Item name='condition_status' label='Status' rules={[{required: true}]}>
            <Select
              options={[
                {value: 'active', label: 'Active'},
                {value: 'monitoring', label: 'Monitoring'},
                {value: 'resolved', label: 'Resolved'},
              ]}
            />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title='Add Reading'
        open={readingModalOpen}
        onCancel={() => setReadingModalOpen(false)}
        onOk={handleSaveReading}
        confirmLoading={saving}
        destroyOnClose
      >
        <Form form={readingForm} layout='vertical'>
          <Form.Item name='reading_date' label='Date' rules={[{required: true}]}>
            <Input type='date' />
          </Form.Item>
          <Form.Item name='reading_type' label='Reading Type' rules={[{required: true}]}>
            <Input placeholder='e.g. blood_glucose, hba1c, blood_pressure, weight' />
          </Form.Item>
          <Form.Item name='reading_value' label='Value' rules={[{required: true}]}>
            <Input placeholder='e.g. 142 or 120/80' />
          </Form.Item>
          <Form.Item name='unit' label='Unit'>
            <Input placeholder='e.g. mg/dL, %, mmHg, kg' />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ChronicConditionPanel

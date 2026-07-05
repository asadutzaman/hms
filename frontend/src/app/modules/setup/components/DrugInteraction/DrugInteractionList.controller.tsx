import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Form, Input, Modal, Select, Table, Tag} from 'antd'
import {PlusOutlined} from '@ant-design/icons'
import {DrugInteractionApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'
import DrugSelect from 'src/app/components/Dropdown/DrugSelect'

const {TextArea} = Input
const {Option} = Select

const severityColor: Record<string, string> = {
  minor: 'default',
  moderate: 'gold',
  severe: 'volcano',
  contraindicated: 'red',
}

// Reference-table CRUD for the drug interaction pairs used by the
// prescription-form interaction check (see DrugInteractionController::check).
const DrugInteractionListController: FC = () => {
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [listData, setListData] = useState<any[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [form] = Form.useForm()
  const [submitting, setSubmitting] = useState(false)

  const loadData = () => {
    setLoading(true)
    DrugInteractionApi.list()
      .then((res: any) => setListData(res?.data?.results || []))
      .catch(() => Message.error('Failed to load drug interactions'))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const openForm = () => {
    form.resetFields()
    form.setFieldsValue({severity: 'moderate'})
    setFormOpen(true)
  }

  const handleSubmit = (values: any) => {
    setSubmitting(true)
    DrugInteractionApi.create(values)
      .then(() => {
        Message.success('Interaction saved')
        setFormOpen(false)
        loadData()
      })
      .catch(() => Message.error('Failed to save interaction'))
      .finally(() => setSubmitting(false))
  }

  const columns = [
    {title: t('Drug A'), dataIndex: 'drug_a_name'},
    {title: t('Drug B'), dataIndex: 'drug_b_name'},
    {
      title: t('Severity'),
      dataIndex: 'severity',
      render: (v: string) => <Tag color={severityColor[v]}>{v}</Tag>,
    },
    {title: t('Description'), dataIndex: 'description'},
  ]

  return (
    <div className='card'>
      <div className='p-6 d-flex justify-content-between'>
        <h4>{t('Drug Interactions')}</h4>
        <Button type='primary' icon={<PlusOutlined />} onClick={openForm}>
          {t('New Interaction')}
        </Button>
      </div>
      <div className='p-6'>
        <Card loading={loading}>
          <Table rowKey='id' columns={columns} dataSource={listData} pagination={false} />
        </Card>
      </div>

      <Modal
        title={t('New Drug Interaction')}
        open={formOpen}
        onCancel={() => setFormOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={submitting}
      >
        <Form form={form} layout='vertical' onFinish={handleSubmit}>
          <Form.Item name='drug_a_id' label={t('Drug A')} rules={[{required: true}]}>
            <DrugSelect drugId={undefined} />
          </Form.Item>
          <Form.Item name='drug_b_id' label={t('Drug B')} rules={[{required: true}]}>
            <DrugSelect drugId={undefined} />
          </Form.Item>
          <Form.Item name='severity' label={t('Severity')} rules={[{required: true}]}>
            <Select>
              <Option value='minor'>Minor</Option>
              <Option value='moderate'>Moderate</Option>
              <Option value='severe'>Severe</Option>
              <Option value='contraindicated'>Contraindicated</Option>
            </Select>
          </Form.Item>
          <Form.Item name='description' label={t('Description')}>
            <TextArea rows={2} />
          </Form.Item>
          <Form.Item name='recommendation' label={t('Recommendation')}>
            <TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default DrugInteractionListController

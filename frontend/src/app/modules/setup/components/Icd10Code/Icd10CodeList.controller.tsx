import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Form, Input, Modal, Switch, Table, Tag} from 'antd'
import {PlusOutlined} from '@ant-design/icons'
import {Icd10CodeApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'

// Reference-table CRUD for the seeded ICD-10 lookup used by Icd10Select
// (diagnosis form typeahead). The seeder covers ~95 common OPD codes;
// admins extend the set here as new codes are needed.
const Icd10CodeListController: FC = () => {
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [listData, setListData] = useState<any[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [form] = Form.useForm()
  const [submitting, setSubmitting] = useState(false)

  const loadData = () => {
    setLoading(true)
    Icd10CodeApi.list({$orderby: 'code asc', $top: 200})
      .then((res: any) => setListData(res?.data?.results || []))
      .catch(() => Message.error('Failed to load ICD-10 codes'))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const openForm = () => {
    form.resetFields()
    form.setFieldsValue({is_billable: true})
    setFormOpen(true)
  }

  const handleSubmit = (values: any) => {
    setSubmitting(true)
    Icd10CodeApi.create(values)
      .then(() => {
        Message.success('ICD-10 code added')
        setFormOpen(false)
        loadData()
      })
      .catch(() => Message.error('Failed to add ICD-10 code'))
      .finally(() => setSubmitting(false))
  }

  const columns = [
    {title: t('Code'), dataIndex: 'code'},
    {title: t('Description'), dataIndex: 'description'},
    {title: t('Category'), dataIndex: 'category', render: (v: string) => v || '-'},
    {
      title: t('Billable'),
      dataIndex: 'is_billable',
      render: (v: boolean) => <Tag color={v ? 'green' : 'default'}>{v ? 'Yes' : 'No'}</Tag>,
    },
  ]

  return (
    <div className='card'>
      <div className='p-6 d-flex justify-content-between'>
        <h4>{t('ICD-10 Codes')}</h4>
        <Button type='primary' icon={<PlusOutlined />} onClick={openForm}>
          {t('New Code')}
        </Button>
      </div>
      <div className='p-6'>
        <Card loading={loading}>
          <Table rowKey='id' columns={columns} dataSource={listData} pagination={{pageSize: 20}} />
        </Card>
      </div>

      <Modal
        title={t('New ICD-10 Code')}
        open={formOpen}
        onCancel={() => setFormOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={submitting}
      >
        <Form form={form} layout='vertical' onFinish={handleSubmit}>
          <Form.Item name='code' label={t('Code')} rules={[{required: true}]}>
            <Input placeholder='e.g. J06.9' />
          </Form.Item>
          <Form.Item name='description' label={t('Description')} rules={[{required: true}]}>
            <Input placeholder='e.g. Acute upper respiratory infection, unspecified' />
          </Form.Item>
          <Form.Item name='category' label={t('Category')}>
            <Input placeholder='e.g. Respiratory' />
          </Form.Item>
          <Form.Item name='is_billable' label={t('Billable')} valuePropName='checked'>
            <Switch />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default Icd10CodeListController

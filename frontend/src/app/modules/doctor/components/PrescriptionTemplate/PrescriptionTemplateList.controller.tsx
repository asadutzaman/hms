import React, {FC, useEffect, useState} from 'react'
import {
  Button,
  Card,
  Col,
  Form,
  Input,
  InputNumber,
  Modal,
  Row,
  Select,
  Switch,
  Table,
  Tag,
} from 'antd'
import {DeleteOutlined, PlusOutlined} from '@ant-design/icons'
import {PrescriptionTemplateApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'
import DrugSelect from 'src/app/components/Dropdown/DrugSelect'

const {Option} = Select

// Self-contained list + create page for reusable prescription templates
// (mirrors the VendorComparison module's single-file pattern) — "Apply
// Template" itself happens inside OpdPrescriptionForm, not here.
const PrescriptionTemplateListController: FC = () => {
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [listData, setListData] = useState<any[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [form] = Form.useForm()
  const [submitting, setSubmitting] = useState(false)

  const loadData = () => {
    setLoading(true)
    PrescriptionTemplateApi.list({$select: 'id,name,is_shared,status'})
      .then((res: any) => {
        setListData(res?.data?.results || [])
      })
      .catch(() => Message.error('Failed to load templates'))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const openForm = () => {
    form.resetFields()
    form.setFieldsValue({items: [{frequency: 'OD', duration_unit: 'days', route: 'oral'}]})
    setFormOpen(true)
  }

  const handleSubmit = (values: any) => {
    setSubmitting(true)
    PrescriptionTemplateApi.create(values)
      .then(() => {
        Message.success('Template created')
        setFormOpen(false)
        loadData()
      })
      .catch(() => Message.error('Failed to create template'))
      .finally(() => setSubmitting(false))
  }

  const columns = [
    {title: t('Name'), dataIndex: 'name'},
    {
      title: t('Visibility'),
      dataIndex: 'is_shared',
      render: (isShared: boolean) => (
        <Tag color={isShared ? 'blue' : 'default'}>{isShared ? 'Shared' : 'Private'}</Tag>
      ),
    },
  ]

  return (
    <div className='card'>
      <div className='p-6 d-flex justify-content-between'>
        <h4>{t('Prescription Templates')}</h4>
        <Button type='primary' icon={<PlusOutlined />} onClick={openForm}>
          {t('New Template')}
        </Button>
      </div>
      <div className='p-6'>
        <Card loading={loading}>
          <Table rowKey='id' columns={columns} dataSource={listData} pagination={false} />
        </Card>
      </div>

      <Modal
        title={t('New Prescription Template')}
        open={formOpen}
        onCancel={() => setFormOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={submitting}
        width={800}
      >
        <Form form={form} layout='vertical' onFinish={handleSubmit}>
          <Row gutter={8}>
            <Col span={16}>
              <Form.Item name='name' label={t('Template Name')} rules={[{required: true}]}>
                <Input placeholder='e.g. Common Cold' />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name='is_shared' label={t('Shared (visible to all doctors)')} valuePropName='checked'>
                <Switch />
              </Form.Item>
            </Col>
          </Row>

          <Form.List name='items'>
            {(fields, {add, remove}) => (
              <>
                {fields.map((field) => (
                  <div key={field.key} className='border rounded p-3 mb-3'>
                    <Row gutter={8}>
                      <Col span={8}>
                        <Form.Item name={[field.name, 'drug_id']} label={t('Drug (catalog)')}>
                          <DrugSelect
                            drugId={undefined}
                            onSelect={(_: any, drug: any) => {
                              form.setFields([
                                {name: ['items', field.name, 'drug_name'], value: drug?.generic_name},
                                {name: ['items', field.name, 'generic_name'], value: drug?.generic_name},
                                {name: ['items', field.name, 'strength'], value: drug?.strength},
                                {name: ['items', field.name, 'dosage_form'], value: drug?.dosage_form},
                              ])
                            }}
                          />
                        </Form.Item>
                        <Form.Item
                          {...field}
                          name={[field.name, 'drug_name']}
                          rules={[{required: true, message: 'Required'}]}
                        >
                          <Input placeholder='Drug name (or pick above)' />
                        </Form.Item>
                      </Col>
                      <Col span={4}>
                        <Form.Item {...field} name={[field.name, 'dose_value']} label={t('Dose')}>
                          <InputNumber min={0} style={{width: '100%'}} />
                        </Form.Item>
                      </Col>
                      <Col span={4}>
                        <Form.Item {...field} name={[field.name, 'frequency']} label={t('Freq')}>
                          <Select>
                            {['OD', 'BD', 'TID', 'QID', 'HS', 'SOS', 'STAT', 'PRN'].map((f) => (
                              <Option key={f} value={f}>{f}</Option>
                            ))}
                          </Select>
                        </Form.Item>
                      </Col>
                      <Col span={4}>
                        <Form.Item {...field} name={[field.name, 'duration_value']} label={t('For')}>
                          <InputNumber min={1} style={{width: '100%'}} />
                        </Form.Item>
                      </Col>
                      <Col span={3}>
                        <Form.Item {...field} name={[field.name, 'route']} label={t('Route')} initialValue='oral'>
                          <Select>
                            {['oral', 'iv', 'im', 'sc', 'topical', 'inhalation', 'rectal', 'other'].map((r) => (
                              <Option key={r} value={r}>{r}</Option>
                            ))}
                          </Select>
                        </Form.Item>
                      </Col>
                      <Col span={1} className='d-flex align-items-center'>
                        {fields.length > 1 && (
                          <Button
                            danger
                            type='text'
                            icon={<DeleteOutlined />}
                            onClick={() => remove(field.name)}
                          />
                        )}
                      </Col>
                    </Row>
                  </div>
                ))}
                <Button
                  type='dashed'
                  block
                  icon={<PlusOutlined />}
                  onClick={() => add({frequency: 'OD', duration_unit: 'days', route: 'oral'})}
                >
                  {t('Add Drug')}
                </Button>
              </>
            )}
          </Form.List>
        </Form>
      </Modal>
    </div>
  )
}

export default PrescriptionTemplateListController

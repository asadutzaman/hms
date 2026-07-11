import React, {FC, useEffect, useState} from 'react'
import {
  Button,
  Card,
  Col,
  Descriptions,
  Form,
  Input,
  InputNumber,
  Modal,
  Popconfirm,
  Row,
  Select,
  Space,
  Switch,
  Table,
  Tag,
} from 'antd'
import {DeleteOutlined, EditOutlined, EyeOutlined, PlusOutlined} from '@ant-design/icons'
import {PrescriptionTemplateApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'
import DrugSelect from 'src/app/components/Dropdown/DrugSelect'

const {Option} = Select

// Self-contained list + form page for reusable prescription templates
// (mirrors the VendorComparison module's single-file pattern) — "Apply
// Template" itself happens inside OpdPrescriptionForm, not here.
const PrescriptionTemplateListController: FC = () => {
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [listData, setListData] = useState<any[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [form] = Form.useForm()
  const [submitting, setSubmitting] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [viewData, setViewData] = useState<any>(null)

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
    setEditingId(null)
    form.resetFields()
    form.setFieldsValue({items: [{frequency: 'OD', duration_unit: 'days', route: 'oral'}]})
    setFormOpen(true)
  }

  const fetchTemplate = (id: number): Promise<any> =>
    PrescriptionTemplateApi.getById(id).then((res: any) => res?.data?.data ?? res?.data)

  const openEdit = (record: any) => {
    fetchTemplate(record.id)
      .then((tpl: any) => {
        setEditingId(record.id)
        form.resetFields()
        form.setFieldsValue({
          name: tpl?.name,
          is_shared: !!tpl?.is_shared,
          items: (tpl?.items || []).map((it: any) => ({
            drug_id: it.drug_id,
            drug_name: it.drug_name,
            generic_name: it.generic_name,
            strength: it.strength,
            dosage_form: it.dosage_form,
            dose_value: it.dose_value,
            dose_unit: it.dose_unit,
            frequency: it.frequency,
            duration_value: it.duration_value,
            duration_unit: it.duration_unit,
            route: it.route,
            instruction: it.instruction,
          })),
        })
        setFormOpen(true)
      })
      .catch(() => Message.error('Failed to load template'))
  }

  const openView = (record: any) => {
    fetchTemplate(record.id)
      .then((tpl: any) => setViewData(tpl))
      .catch(() => Message.error('Failed to load template'))
  }

  const handleDelete = (id: number) => {
    PrescriptionTemplateApi.delete(id)
      .then(() => {
        Message.success('Template deleted')
        loadData()
      })
      .catch(() => Message.error('Failed to delete template'))
  }

  const handleSubmit = (values: any) => {
    setSubmitting(true)
    const request = editingId
      ? PrescriptionTemplateApi.update(editingId, values)
      : PrescriptionTemplateApi.create(values)
    request
      .then(() => {
        Message.success(editingId ? 'Template updated' : 'Template created')
        setFormOpen(false)
        loadData()
      })
      .catch(() => Message.error(editingId ? 'Failed to update template' : 'Failed to create template'))
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
    {
      title: t('Actions'),
      key: 'actions',
      width: 160,
      render: (_: any, record: any) => (
        <Space>
          <Button size='small' icon={<EyeOutlined />} onClick={() => openView(record)} />
          <Button size='small' icon={<EditOutlined />} onClick={() => openEdit(record)} />
          <Popconfirm
            title={t('Delete this template?')}
            onConfirm={() => handleDelete(record.id)}
            okText={t('Delete')}
            okButtonProps={{danger: true}}
          >
            <Button size='small' danger icon={<DeleteOutlined />} />
          </Popconfirm>
        </Space>
      ),
    },
  ]

  const viewItemColumns = [
    {title: t('Drug'), dataIndex: 'drug_name'},
    {title: t('Strength'), dataIndex: 'strength'},
    {
      title: t('Dose'),
      key: 'dose',
      render: (_: any, it: any) => [it.dose_value, it.dose_unit].filter(Boolean).join(' '),
    },
    {title: t('Freq'), dataIndex: 'frequency'},
    {
      title: t('Duration'),
      key: 'duration',
      render: (_: any, it: any) =>
        it.duration_value ? `${it.duration_value} ${it.duration_unit || ''}`.trim() : '',
    },
    {title: t('Route'), dataIndex: 'route'},
    {title: t('Instruction'), dataIndex: 'instruction'},
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
        title={editingId ? t('Edit Prescription Template') : t('New Prescription Template')}
        open={formOpen}
        onCancel={() => setFormOpen(false)}
        onOk={() => form.submit()}
        confirmLoading={submitting}
        width={900}
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
                      <Col span={7}>
                        <Form.Item
                          name={[field.name, 'drug_id']}
                          label={t('Drug (catalog)')}
                          valuePropName='drugId'
                        >
                          <DrugSelect
                            drugId={undefined}
                            serverSearch
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
                        <Form.Item {...field} name={[field.name, 'strength']} label={t('Strength')}>
                          <Input placeholder='e.g. 500mg' />
                        </Form.Item>
                      </Col>
                      <Col span={3}>
                        <Form.Item {...field} name={[field.name, 'dose_value']} label={t('Dose')}>
                          <InputNumber min={0} style={{width: '100%'}} />
                        </Form.Item>
                      </Col>
                      <Col span={3}>
                        <Form.Item {...field} name={[field.name, 'dose_unit']} label={t('Unit')}>
                          <Input placeholder='tab' />
                        </Form.Item>
                      </Col>
                      <Col span={3}>
                        <Form.Item {...field} name={[field.name, 'frequency']} label={t('Freq')}>
                          <Select>
                            {['OD', 'BD', 'TID', 'QID', 'HS', 'SOS', 'STAT', 'PRN'].map((f) => (
                              <Option key={f} value={f}>{f}</Option>
                            ))}
                          </Select>
                        </Form.Item>
                      </Col>
                      <Col span={3}>
                        <Form.Item {...field} name={[field.name, 'duration_value']} label={t('For')}>
                          <InputNumber min={1} style={{width: '100%'}} />
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
                    <Row gutter={8}>
                      <Col span={4}>
                        <Form.Item
                          {...field}
                          name={[field.name, 'duration_unit']}
                          label={t('Duration Unit')}
                          initialValue='days'
                        >
                          <Select>
                            <Option value='days'>Days</Option>
                            <Option value='weeks'>Weeks</Option>
                            <Option value='months'>Months</Option>
                          </Select>
                        </Form.Item>
                      </Col>
                      <Col span={4}>
                        <Form.Item {...field} name={[field.name, 'route']} label={t('Route')} initialValue='oral'>
                          <Select>
                            {['oral', 'iv', 'im', 'sc', 'topical', 'inhalation', 'rectal', 'other'].map((r) => (
                              <Option key={r} value={r}>{r}</Option>
                            ))}
                          </Select>
                        </Form.Item>
                      </Col>
                      <Col span={16}>
                        <Form.Item {...field} name={[field.name, 'instruction']} label={t('Instruction')}>
                          <Input placeholder='e.g. After meals' />
                        </Form.Item>
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

      <Modal
        title={viewData?.name || t('Prescription Template')}
        open={!!viewData}
        onCancel={() => setViewData(null)}
        footer={
          <Button onClick={() => setViewData(null)}>{t('Close')}</Button>
        }
        width={900}
      >
        <Descriptions size='small' column={2} className='mb-4'>
          <Descriptions.Item label={t('Name')}>{viewData?.name}</Descriptions.Item>
          <Descriptions.Item label={t('Visibility')}>
            <Tag color={viewData?.is_shared ? 'blue' : 'default'}>
              {viewData?.is_shared ? 'Shared' : 'Private'}
            </Tag>
          </Descriptions.Item>
        </Descriptions>
        <Table
          rowKey={(it: any) => it.id ?? `${it.drug_name}-${it.sequence}`}
          columns={viewItemColumns}
          dataSource={viewData?.items || []}
          pagination={false}
          size='small'
        />
      </Modal>
    </div>
  )
}

export default PrescriptionTemplateListController

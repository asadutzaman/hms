import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Col, Form, Input, Modal, Row, Select, Switch, Table, Tag} from 'antd'
import {DeleteOutlined, PlusOutlined} from '@ant-design/icons'
import {DiagnosisTemplateApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'
import Icd10Select from 'src/app/components/Dropdown/Icd10Select'

const {Option} = Select

// Self-contained list + create page for reusable diagnosis templates
// (mirrors PrescriptionTemplateListController's single-file pattern) —
// "Apply Template" itself happens inside the OPD visit's diagnosis modal.
const DiagnosisTemplateListController: FC = () => {
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [listData, setListData] = useState<any[]>([])
  const [formOpen, setFormOpen] = useState(false)
  const [form] = Form.useForm()
  const [submitting, setSubmitting] = useState(false)

  const loadData = () => {
    setLoading(true)
    DiagnosisTemplateApi.list({$select: 'id,name,is_shared,status'})
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
    form.setFieldsValue({items: [{diagnosis_type: 'primary'}]})
    setFormOpen(true)
  }

  const handleSubmit = (values: any) => {
    setSubmitting(true)
    DiagnosisTemplateApi.create(values)
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
        <h4>{t('Diagnosis Templates')}</h4>
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
        title={t('New Diagnosis Template')}
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
                <Input placeholder='e.g. URTI Workup' />
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
                      <Col span={9}>
                        <Form.Item name={[field.name, 'icd10_id']} label={t('ICD-10 Lookup')}>
                          <Icd10Select
                            icd10Id={undefined}
                            onSelect={(_: any, icd: any) => {
                              form.setFields([
                                {name: ['items', field.name, 'diagnosis_name'], value: icd?.description},
                              ])
                            }}
                          />
                        </Form.Item>
                      </Col>
                      <Col span={9}>
                        <Form.Item
                          {...field}
                          name={[field.name, 'diagnosis_name']}
                          label={t('Diagnosis Name')}
                          rules={[{required: true, message: 'Required'}]}
                        >
                          <Input />
                        </Form.Item>
                      </Col>
                      <Col span={5}>
                        <Form.Item {...field} name={[field.name, 'diagnosis_type']} label={t('Type')} initialValue='primary'>
                          <Select>
                            <Option value='primary'>Primary</Option>
                            <Option value='secondary'>Secondary</Option>
                            <Option value='differential'>Differential</Option>
                            <Option value='final'>Final</Option>
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
                <Button type='dashed' block icon={<PlusOutlined />} onClick={() => add({diagnosis_type: 'primary'})}>
                  {t('Add Diagnosis')}
                </Button>
              </>
            )}
          </Form.List>
        </Form>
      </Modal>
    </div>
  )
}

export default DiagnosisTemplateListController

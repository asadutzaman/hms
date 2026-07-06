import React, {FC, useEffect, useState} from 'react'
import {
  Card,
  Form,
  Input,
  InputNumber,
  Select,
  DatePicker,
  Button,
  Table,
  Tag,
  Empty,
  notification,
  Modal,
  Popconfirm,
  Space,
} from 'antd'
import {PlusOutlined} from '@ant-design/icons'
import {IpdMedicationOrderApi, IpdMedicationAdministrationApi} from 'src/app/api'
import {DateTimeUtils} from 'src/app/utils'

const {Option} = Select

interface MedicationTabProps {
  admissionId: number
}

const statusColor = (status: string): string => {
  switch (status) {
    case 'scheduled':
      return 'blue'
    case 'given':
      return 'green'
    case 'held':
      return 'gold'
    case 'refused':
      return 'red'
    case 'missed':
      return 'default'
    default:
      return 'default'
  }
}

const MedicationTab: FC<MedicationTabProps> = ({admissionId}) => {
  const [orders, setOrders] = useState<any[]>([])
  const [orderForm] = Form.useForm()
  const [orderModalOpen, setOrderModalOpen] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  const loadOrders = () => {
    IpdMedicationOrderApi.byAdmission(admissionId)
      .then((res: any) => setOrders(res?.data?.data ?? res?.data ?? []))
      .catch(() => setOrders([]))
  }

  useEffect(() => {
    loadOrders()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [admissionId])

  const openOrderModal = () => {
    orderForm.resetFields()
    orderForm.setFieldsValue({frequency: 'BD', route: 'oral', start_date: DateTimeUtils.formatDate(new Date())})
    setOrderModalOpen(true)
  }

  const handlePlaceOrder = async (values: any) => {
    setSubmitting(true)
    try {
      const payload = {
        ...values,
        admission_id: admissionId,
        start_date: values.start_date?.format ? values.start_date.format('YYYY-MM-DD') : values.start_date,
        end_date: values.end_date?.format ? values.end_date.format('YYYY-MM-DD') : undefined,
      }
      await IpdMedicationOrderApi.create(payload)
      notification.success({message: 'Medication order placed'})
      setOrderModalOpen(false)
      loadOrders()
    } catch (e: any) {
      notification.error({message: 'Failed to place order', description: e?.response?.data?.message})
    } finally {
      setSubmitting(false)
    }
  }

  const handleDiscontinue = async (orderId: number) => {
    try {
      await IpdMedicationOrderApi.discontinue(orderId, {reason: 'Discontinued by doctor'})
      notification.success({message: 'Order discontinued'})
      loadOrders()
    } catch (e: any) {
      notification.error({message: 'Failed to discontinue order', description: e?.response?.data?.message})
    }
  }

  const handleAdministerPrn = async (orderId: number) => {
    try {
      await IpdMedicationOrderApi.administerPrn(orderId, {})
      notification.success({message: 'PRN dose recorded'})
      loadOrders()
    } catch (e: any) {
      notification.error({message: 'Failed to record dose', description: e?.response?.data?.message})
    }
  }

  const handleRecordSlot = async (administrationId: number, administration_status: string) => {
    try {
      await IpdMedicationAdministrationApi.record(administrationId, {administration_status})
      notification.success({message: `Marked as ${administration_status}`})
      loadOrders()
    } catch (e: any) {
      notification.error({message: 'Failed to record administration', description: e?.response?.data?.message})
    }
  }

  const administrationColumns = [
    {
      title: 'Scheduled At',
      dataIndex: 'scheduled_at',
      key: 'scheduled_at',
      render: (v: string) => (v ? DateTimeUtils.formatDateTimeA(v) : 'PRN (ad-hoc)'),
    },
    {
      title: 'Status',
      dataIndex: 'administration_status_label',
      key: 'administration_status_label',
      render: (v: string, row: any) => <Tag color={statusColor(row.administration_status)}>{v}</Tag>,
    },
    {title: 'Administered At', dataIndex: 'administered_at', key: 'administered_at', render: (v: string) => (v ? DateTimeUtils.formatDateTimeA(v) : '-')},
    {
      title: 'Action',
      key: 'action',
      render: (_: any, row: any) =>
        row.administration_status === 'scheduled' ? (
          <Space size='small'>
            <Button size='small' type='primary' onClick={() => handleRecordSlot(row.id, 'given')}>
              Given
            </Button>
            <Button size='small' onClick={() => handleRecordSlot(row.id, 'held')}>
              Held
            </Button>
            <Button size='small' danger onClick={() => handleRecordSlot(row.id, 'refused')}>
              Refused
            </Button>
          </Space>
        ) : (
          '-'
        ),
    },
  ]

  return (
    <div>
      <div className='d-flex justify-content-between align-items-center mb-3'>
        <h5 className='mb-0'>Medication Administration Record (MAR)</h5>
        <Button size='small' type='primary' icon={<PlusOutlined />} onClick={openOrderModal}>
          Place Order
        </Button>
      </div>

      {orders.length === 0 ? (
        <Empty description='No medication orders yet' />
      ) : (
        orders.map((order: any) => (
          <Card
            key={order.id}
            size='small'
            className='mb-3'
            title={
              <span>
                {order.drug_name} {order.strength ? `(${order.strength})` : ''}{' '}
                {order.dose_value ? `— ${order.dose_value}${order.dose_unit || ''}` : ''} · {order.frequency} · {order.route}
              </span>
            }
            extra={
              <Space>
                <Tag color={order.order_status === 'active' ? 'blue' : order.order_status === 'discontinued' ? 'red' : 'default'} className='text-capitalize'>
                  {order.order_status_label}
                </Tag>
                {order.is_prn && order.order_status === 'active' && (
                  <Button size='small' onClick={() => handleAdministerPrn(order.id)}>
                    Give PRN Dose
                  </Button>
                )}
                {order.order_status === 'active' && (
                  <Popconfirm title='Discontinue this order?' onConfirm={() => handleDiscontinue(order.id)}>
                    <Button size='small' danger>
                      Discontinue
                    </Button>
                  </Popconfirm>
                )}
              </Space>
            }
          >
            {order.instruction && <div className='text-muted fs-8 mb-2'>{order.instruction}</div>}
            {(order.administrations || []).length > 0 ? (
              <Table
                rowKey='id'
                size='small'
                columns={administrationColumns}
                dataSource={order.administrations}
                pagination={{pageSize: 5}}
              />
            ) : (
              <span className='text-muted fs-8'>No administration slots (PRN — given on demand).</span>
            )}
          </Card>
        ))
      )}

      <Modal title='Place Medication Order' open={orderModalOpen} onCancel={() => setOrderModalOpen(false)} onOk={() => orderForm.submit()} confirmLoading={submitting}>
        <Form form={orderForm} layout='vertical' onFinish={handlePlaceOrder}>
          <Form.Item name='drug_name' label='Drug Name' rules={[{required: true, message: 'Drug name is required'}]}>
            <Input placeholder='e.g. Paracetamol' />
          </Form.Item>
          <Form.Item name='strength' label='Strength'>
            <Input placeholder='e.g. 500mg' />
          </Form.Item>
          <Space wrap>
            <Form.Item name='dose_value' label='Dose'>
              <InputNumber min={0} style={{width: 120}} />
            </Form.Item>
            <Form.Item name='dose_unit' label='Unit'>
              <Input placeholder='mg/mL/tab' style={{width: 120}} />
            </Form.Item>
            <Form.Item name='route' label='Route'>
              <Select style={{width: 140}}>
                {['oral', 'iv', 'im', 'sc', 'topical', 'inhalation', 'rectal', 'other'].map((r) => (
                  <Option key={r} value={r}>{r}</Option>
                ))}
              </Select>
            </Form.Item>
          </Space>
          <Space wrap>
            <Form.Item name='frequency' label='Frequency' rules={[{required: true}]}>
              <Select style={{width: 140}}>
                {['OD', 'BD', 'TID', 'QID', 'HS', 'STAT', 'SOS', 'PRN'].map((f) => (
                  <Option key={f} value={f}>{f}</Option>
                ))}
              </Select>
            </Form.Item>
            <Form.Item name='duration_value' label='Duration'>
              <InputNumber min={1} style={{width: 100}} />
            </Form.Item>
            <Form.Item name='duration_unit' label='Unit' initialValue='days'>
              <Select style={{width: 120}}>
                <Option value='days'>Days</Option>
                <Option value='weeks'>Weeks</Option>
              </Select>
            </Form.Item>
          </Space>
          <Form.Item name='start_date' label='Start Date' rules={[{required: true}]}>
            <DatePicker style={{width: '100%'}} />
          </Form.Item>
          <Form.Item name='instruction' label='Instruction'>
            <Input placeholder='e.g. Take after meals' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default MedicationTab

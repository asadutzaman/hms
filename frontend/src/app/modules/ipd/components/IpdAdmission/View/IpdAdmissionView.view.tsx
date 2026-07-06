import React, {FC, useEffect, useState} from 'react'
import {
  Badge,
  Descriptions,
  Tag,
  Divider,
  Tabs,
  Button,
  Space,
  Modal,
  Form,
  InputNumber,
  Input,
  Select,
  Table,
  Empty,
  notification,
  Popconfirm,
  Row,
  Col,
} from 'antd'
import {
  PlusOutlined,
  DeleteOutlined,
  DollarOutlined,
  PercentageOutlined,
  CheckOutlined,
  CloseOutlined,
  SwapOutlined,
  LogoutOutlined,
  ReloadOutlined,
} from '@ant-design/icons'
import {DateTimeUtils} from 'src/app/utils'
import {IpdAdmissionApi, IpdBillApi, IpdAdvancePaymentApi, BedApi} from 'src/app/api'
import AuditLogPanel from 'src/app/components/AuditLog/AuditLogPanel'
import NursingTab from '../Tabs/NursingTab'
import MedicationTab from '../Tabs/MedicationTab'
import DischargeTab from '../Tabs/DischargeTab'
import WardSelect from 'src/app/components/Dropdown/WardSelect'
import {usePermissionContext} from 'src/app/hooks/context/usePermissionContext'

const {TextArea} = Input
const {Option} = Select

const statusColor = (status: string): string => {
  switch (status) {
    case 'admitted':
      return 'blue'
    case 'discharged':
      return 'green'
    case 'dama':
      return 'gold'
    case 'deceased':
      return 'default'
    default:
      return 'default'
  }
}

const IpdAdmissionView: FC<any> = ({itemData, handleCallbackFunc}) => {
  const {hasPermission} = usePermissionContext()
  const [submitting, setSubmitting] = useState(false)
  const [refreshingCharges, setRefreshingCharges] = useState(false)

  const [addItemModalOpen, setAddItemModalOpen] = useState(false)
  const [paymentModalOpen, setPaymentModalOpen] = useState(false)
  const [discountModalOpen, setDiscountModalOpen] = useState(false)
  const [advanceModalOpen, setAdvanceModalOpen] = useState(false)
  const [transferModalOpen, setTransferModalOpen] = useState(false)
  const [exitModalOpen, setExitModalOpen] = useState(false)

  const [addItemForm] = Form.useForm()
  const [paymentForm] = Form.useForm()
  const [discountForm] = Form.useForm()
  const [advanceForm] = Form.useForm()
  const [transferForm] = Form.useForm()
  const [exitForm] = Form.useForm()

  const [transferBeds, setTransferBeds] = useState<any[]>([])
  const [loadingTransferBeds, setLoadingTransferBeds] = useState(false)
  const transferWardId = Form.useWatch('ward_id', transferForm)

  const [advances, setAdvances] = useState<any[]>([])

  const loadAdvances = () => {
    if (!itemData?.id) return
    IpdAdvancePaymentApi.byAdmission(itemData.id)
      .then((res: any) => setAdvances(res?.data?.data || res?.data || []))
      .catch(() => setAdvances([]))
  }

  useEffect(() => {
    loadAdvances()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [itemData?.id])

  useEffect(() => {
    if (!transferModalOpen || !transferWardId) {
      return
    }
    setLoadingTransferBeds(true)
    BedApi.list({$filter: `ward_id=${transferWardId}`, $top: 200})
      .then((res: any) => {
        const all = res?.data?.results || []
        setTransferBeds(all.filter((b: any) => b.ward_id === transferWardId && b.bed_status === 'vacant'))
      })
      .catch(() => setTransferBeds([]))
      .finally(() => setLoadingTransferBeds(false))
  }, [transferWardId, transferModalOpen])

  if (!itemData || !itemData.id) {
    return <div className='p-6'>No admission selected.</div>
  }

  const isAdmitted = itemData.admission_status === 'admitted'
  const bill = itemData.bill

  const notifyReload = () => {
    handleCallbackFunc?.('singleAction', 'reloadView')
    handleCallbackFunc?.('singleAction', 'reloadListing')
    loadAdvances()
  }

  const errMsg = (e: any) => e?.response?.data?.message || e?.response?.data || e?.message || 'Unknown error'

  // ── Room charges / billing actions ──────────────────────────────────────
  const handleRefreshRoomCharges = async () => {
    if (!bill?.id) return
    setRefreshingCharges(true)
    try {
      await IpdBillApi.refreshRoomCharges(bill.id)
      notification.success({message: 'Room charges refreshed'})
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to refresh room charges', description: errMsg(e)})
    } finally {
      setRefreshingCharges(false)
    }
  }

  const handleAddItem = async (values: any) => {
    setSubmitting(true)
    try {
      await IpdBillApi.addItem(bill.id, values)
      notification.success({message: 'Charge added'})
      setAddItemModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to add charge', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const handleRemoveItem = async (itemId: number) => {
    try {
      await IpdBillApi.removeItem(bill.id, itemId)
      notification.success({message: 'Charge removed'})
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to remove charge', description: errMsg(e)})
    }
  }

  const openPaymentModal = () => {
    paymentForm.resetFields()
    paymentForm.setFieldsValue({payments: [{payment_method: 'cash'}]})
    setPaymentModalOpen(true)
  }

  const handleRecordPayment = async (values: any) => {
    setSubmitting(true)
    try {
      await IpdBillApi.recordSplitPayment(bill.id, {payments: values.payments || []})
      notification.success({message: 'Payment recorded'})
      setPaymentModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to record payment', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const handleApplyDiscount = async (values: any) => {
    setSubmitting(true)
    try {
      await IpdBillApi.applyDiscount(bill.id, values)
      notification.success({message: 'Discount applied'})
      setDiscountModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to apply discount', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const handleApproveDiscount = async () => {
    setSubmitting(true)
    try {
      await IpdBillApi.approveDiscount(bill.id)
      notification.success({message: 'Discount approved'})
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to approve discount', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const handleRejectDiscount = async () => {
    setSubmitting(true)
    try {
      await IpdBillApi.rejectDiscount(bill.id, {reason: 'Rejected by supervisor'})
      notification.success({message: 'Discount rejected'})
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to reject discount', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const handleWaive = async () => {
    setSubmitting(true)
    try {
      await IpdBillApi.waive(bill.id, {reason: 'Waived by supervisor'})
      notification.success({message: 'Bill waived'})
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to waive bill', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  // ── Advance payments ─────────────────────────────────────────────────────
  const openAdvanceModal = () => {
    advanceForm.resetFields()
    advanceForm.setFieldsValue({payment_method: 'cash'})
    setAdvanceModalOpen(true)
  }

  const handleReceiveAdvance = async (values: any) => {
    setSubmitting(true)
    try {
      await IpdAdvancePaymentApi.create({...values, admission_id: itemData.id})
      notification.success({message: 'Advance received'})
      setAdvanceModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to record advance', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const handleApplyAdvance = async (advanceId: number) => {
    try {
      await IpdAdvancePaymentApi.apply(advanceId, {})
      notification.success({message: 'Advance applied to bill'})
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to apply advance', description: errMsg(e)})
    }
  }

  // ── Transfer bed ──────────────────────────────────────────────────────────
  const openTransferModal = () => {
    transferForm.resetFields()
    setTransferBeds([])
    setTransferModalOpen(true)
  }

  const handleTransferBed = async (values: any) => {
    setSubmitting(true)
    try {
      await IpdAdmissionApi.transferBed(itemData.id, {bed_id: values.bed_id, reason: values.reason})
      notification.success({message: 'Bed transferred'})
      setTransferModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to transfer bed', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  // ── Exit (discharge / DAMA / deceased) ───────────────────────────────────
  const openExitModal = () => {
    exitForm.resetFields()
    exitForm.setFieldsValue({status: 'discharged'})
    setExitModalOpen(true)
  }

  const handleExit = async (values: any) => {
    setSubmitting(true)
    try {
      await IpdAdmissionApi.exit(itemData.id, values)
      notification.success({message: 'Admission closed'})
      setExitModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to close admission', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  // ── Overview tab ──────────────────────────────────────────────────────────
  const overviewTab = (
    <div>
      <Descriptions title='Admission Information' bordered column={2} size='small' className='mb-6'>
        <Descriptions.Item label='Admission No'>{itemData.admission_no}</Descriptions.Item>
        <Descriptions.Item label='Admission Type'>
          <span className='text-capitalize'>{itemData.admission_type}</span>
        </Descriptions.Item>
        <Descriptions.Item label='Ward'>{itemData.ward_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='Bed'>{itemData.bed_number || '-'}</Descriptions.Item>
        <Descriptions.Item label='Attending Doctor'>{itemData.attending_doctor_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='Admission Date'>
          {itemData.admission_date ? DateTimeUtils.formatDateTimeA(itemData.admission_date) : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Expected Discharge'>
          {itemData.expected_discharge_date ? DateTimeUtils.formatDate(itemData.expected_discharge_date) : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Discharge Date'>
          {itemData.discharge_date ? DateTimeUtils.formatDateTimeA(itemData.discharge_date) : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Status'>
          <Badge color={statusColor(itemData.admission_status)} className='text-capitalize'>
            {itemData.admission_status}
          </Badge>
        </Descriptions.Item>
        {itemData.discharge_override_reason && (
          <Descriptions.Item label='Discharge Override Reason' span={2}>
            {itemData.discharge_override_reason}
          </Descriptions.Item>
        )}
      </Descriptions>

      <Descriptions title='Patient Information' bordered column={2} size='small' className='mb-6'>
        <Descriptions.Item label='Patient Name'>{itemData.patient_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='MRN'>{itemData.mrn || '-'}</Descriptions.Item>
        <Descriptions.Item label='Diagnosis at Admission' span={2}>
          <span style={{whiteSpace: 'pre-wrap'}}>{itemData.diagnosis_at_admission || '-'}</span>
        </Descriptions.Item>
      </Descriptions>

      <Divider />
      <Descriptions column={2} size='small' className='text-muted fs-7'>
        <Descriptions.Item label='Created By'>{itemData.created_by_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='Created At'>
          {itemData.created_at ? DateTimeUtils.formatDateTime(itemData.created_at) : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Updated By'>{itemData.updated_by_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='Updated At'>
          {itemData.updated_at ? DateTimeUtils.formatDateTime(itemData.updated_at) : '-'}
        </Descriptions.Item>
      </Descriptions>
    </div>
  )

  // ── Billing tab ───────────────────────────────────────────────────────────
  const billItemColumns = [
    {title: 'Type', dataIndex: 'item_type_label', key: 'item_type_label', width: '16%'},
    {title: 'Description', dataIndex: 'description', key: 'description'},
    {title: 'Qty', dataIndex: 'quantity', key: 'quantity', width: '8%', align: 'right' as const},
    {title: 'Unit Price', dataIndex: 'unit_price', key: 'unit_price', width: '14%', align: 'right' as const},
    {title: 'Amount', dataIndex: 'amount', key: 'amount', width: '14%', align: 'right' as const},
    ...(bill && !bill.is_finalized
      ? [
          {
            title: 'Action',
            key: 'action',
            width: '8%',
            render: (_: any, row: any) => (
              <Popconfirm title='Remove this charge?' onConfirm={() => handleRemoveItem(row.id)}>
                <Button size='small' danger type='text' icon={<DeleteOutlined />} />
              </Popconfirm>
            ),
          },
        ]
      : []),
  ]

  const billPaymentColumns = [
    {title: 'Method', dataIndex: 'payment_method_label', key: 'payment_method_label', width: '20%'},
    {title: 'Amount', dataIndex: 'amount', key: 'amount', width: '15%', align: 'right' as const},
    {title: 'Reference', dataIndex: 'reference_no', key: 'reference_no', render: (v: string) => v || '-'},
    {
      title: 'Paid At',
      dataIndex: 'paid_at',
      key: 'paid_at',
      width: '20%',
      render: (v: string) => (v ? DateTimeUtils.formatDateTimeA(v) : '-'),
    },
  ]

  const advanceColumns = [
    {title: 'Amount', dataIndex: 'amount', key: 'amount', width: '15%', align: 'right' as const},
    {title: 'Applied', dataIndex: 'applied_amount', key: 'applied_amount', width: '15%', align: 'right' as const},
    {title: 'Method', dataIndex: 'payment_method_label', key: 'payment_method_label', width: '15%'},
    {
      title: 'Status',
      dataIndex: 'advance_status_label',
      key: 'advance_status_label',
      width: '15%',
      render: (v: string) => <Tag color={v === 'Fully Applied' ? 'green' : v === 'Received' ? 'blue' : 'gold'}>{v}</Tag>,
    },
    {
      title: 'Received At',
      dataIndex: 'received_at',
      key: 'received_at',
      width: '15%',
      render: (v: string) => (v ? DateTimeUtils.formatDateTimeA(v) : '-'),
    },
    {
      title: 'Action',
      key: 'action',
      render: (_: any, row: any) =>
        Number(row.unapplied_amount) > 0 && bill ? (
          <Popconfirm title={`Apply ${row.unapplied_amount} to bill?`} onConfirm={() => handleApplyAdvance(row.id)}>
            <Button size='small'>Apply to Bill</Button>
          </Popconfirm>
        ) : (
          '-'
        ),
    },
  ]

  const billingTab = (
    <div>
      {!bill ? (
        <Empty description='No bill exists for this admission yet' />
      ) : (
        <>
          <div className='d-flex justify-content-between align-items-center mb-3'>
            <div>
              <h5 className='mb-0'>{bill.bill_no}</h5>
              <span className='text-muted fs-7'>
                {bill.billed_at ? DateTimeUtils.formatDateTimeA(bill.billed_at) : ''}
              </span>
            </div>
            <Space wrap>
              <Tag color={bill.is_paid ? 'green' : bill.is_partial ? 'gold' : bill.is_waived ? 'default' : 'red'}>
                {bill.bill_status_label}
              </Tag>
              <Button size='small' icon={<ReloadOutlined />} loading={refreshingCharges} onClick={handleRefreshRoomCharges}>
                Refresh Room Charges
              </Button>
              {!bill.is_finalized && (
                <Button
                  size='small'
                  icon={<PlusOutlined />}
                  onClick={() => {
                    addItemForm.resetFields()
                    addItemForm.setFieldsValue({item_type: 'other', quantity: 1})
                    setAddItemModalOpen(true)
                  }}
                >
                  Add Charge
                </Button>
              )}
              {!bill.is_finalized && bill.discount_status !== 'pending_approval' && (
                <Button
                  size='small'
                  icon={<PercentageOutlined />}
                  onClick={() => {
                    discountForm.resetFields()
                    setDiscountModalOpen(true)
                  }}
                >
                  Apply Discount
                </Button>
              )}
              {bill.balance > 0 && (
                <Button size='small' type='primary' icon={<DollarOutlined />} onClick={openPaymentModal}>
                  Collect Payment
                </Button>
              )}
              {!bill.is_finalized && bill.balance > 0 && (
                <Popconfirm title='Waive the remaining balance on this bill?' onConfirm={handleWaive}>
                  <Button size='small' danger>
                    Waive
                  </Button>
                </Popconfirm>
              )}
            </Space>
          </div>

          <Table
            rowKey='id'
            size='small'
            className='mb-4'
            columns={billItemColumns}
            dataSource={bill.items || []}
            pagination={false}
          />

          <Row gutter={16} className='mb-4'>
            <Col span={12}>
              <Descriptions bordered size='small' column={1}>
                <Descriptions.Item label='Subtotal'>{bill.subtotal}</Descriptions.Item>
                <Descriptions.Item label='Discount'>{bill.discount}</Descriptions.Item>
                <Descriptions.Item label='Tax'>{bill.tax}</Descriptions.Item>
                <Descriptions.Item label='Total'>{bill.total}</Descriptions.Item>
                <Descriptions.Item label='Paid'>{bill.paid}</Descriptions.Item>
                <Descriptions.Item label='Balance'>{bill.balance}</Descriptions.Item>
              </Descriptions>
            </Col>
            {bill.discount_status && bill.discount_status !== 'none' && (
              <Col span={12}>
                <Descriptions bordered size='small' column={1} title='Discount / Concession'>
                  <Descriptions.Item label='Status'>
                    <Tag
                      color={
                        bill.discount_status === 'approved'
                          ? 'green'
                          : bill.discount_status === 'pending_approval'
                          ? 'gold'
                          : bill.discount_status === 'rejected'
                          ? 'red'
                          : 'default'
                      }
                      className='text-capitalize'
                    >
                      {bill.discount_status.replace('_', ' ')}
                    </Tag>
                  </Descriptions.Item>
                  {bill.discount_status === 'pending_approval' && (
                    <Descriptions.Item label='Pending Amount'>{bill.pending_discount}</Descriptions.Item>
                  )}
                  <Descriptions.Item label='Reason'>{bill.discount_reason || '-'}</Descriptions.Item>
                  {bill.discount_status === 'pending_approval' && hasPermission('auth:ipd-bill:approveDiscount') && (
                    <Descriptions.Item label='Action'>
                      <Space>
                        <Button
                          size='small'
                          type='primary'
                          icon={<CheckOutlined />}
                          loading={submitting}
                          onClick={handleApproveDiscount}
                        >
                          Approve
                        </Button>
                        <Popconfirm title='Reject this discount request?' onConfirm={handleRejectDiscount}>
                          <Button size='small' danger icon={<CloseOutlined />} loading={submitting}>
                            Reject
                          </Button>
                        </Popconfirm>
                      </Space>
                    </Descriptions.Item>
                  )}
                </Descriptions>
              </Col>
            )}
          </Row>

          <h5>Payments</h5>
          {(bill.payments || []).length > 0 ? (
            <Table
              rowKey='id'
              size='small'
              className='mb-6'
              columns={billPaymentColumns}
              dataSource={bill.payments || []}
              pagination={false}
            />
          ) : (
            <Empty description='No payments recorded yet' className='mb-6' />
          )}

          <div className='d-flex justify-content-between align-items-center mb-2'>
            <h5 className='mb-0'>Advance Payments</h5>
            <Button size='small' icon={<PlusOutlined />} onClick={openAdvanceModal}>
              Receive Advance
            </Button>
          </div>
          {advances.length > 0 ? (
            <Table rowKey='id' size='small' columns={advanceColumns} dataSource={advances} pagination={false} />
          ) : (
            <Empty description='No advance payments received yet' />
          )}
        </>
      )}
    </div>
  )

  const tabItems = [
    {key: 'overview', label: 'Overview', children: overviewTab},
    {key: 'nursing', label: 'Nursing', children: <NursingTab admissionId={itemData.id} />},
    {key: 'medications', label: 'Medications (MAR)', children: <MedicationTab admissionId={itemData.id} />},
    {key: 'billing', label: 'Billing', children: billingTab},
    {
      key: 'discharge',
      label: 'Discharge',
      children: <DischargeTab admissionId={itemData.id} admissionStatus={itemData.admission_status} />,
    },
    {
      key: 'audit',
      label: 'Audit Log',
      children: <AuditLogPanel fetchFn={() => IpdAdmissionApi.auditLog(itemData.id)} reloadKey={itemData.id} />,
    },
  ]

  return (
    <div className='view-page-content p-6'>
      {/* ============= HEADER ============= */}
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <div>
          <h3 className='mb-1'>{itemData.admission_no}</h3>
          <div className='text-muted fs-7'>
            {itemData.patient_name} · {itemData.ward_name} / Bed {itemData.bed_number}
          </div>
        </div>
        <Space>
          {isAdmitted && hasPermission('auth:ipd-admission:edit') && (
            <Button size='small' icon={<SwapOutlined />} onClick={openTransferModal}>
              Transfer Bed
            </Button>
          )}
          {isAdmitted && hasPermission('auth:ipd-admission:exit') && (
            <Button size='small' danger icon={<LogoutOutlined />} onClick={openExitModal}>
              Discharge / Exit
            </Button>
          )}
          <Badge color={statusColor(itemData.admission_status)} className='text-capitalize fs-6'>
            {itemData.admission_status}
          </Badge>
        </Space>
      </div>

      <Tabs items={tabItems} type='card' size='small' />

      {/* ============= ADD CHARGE MODAL ============= */}
      <Modal
        title='Add Charge'
        open={addItemModalOpen}
        onCancel={() => setAddItemModalOpen(false)}
        onOk={() => addItemForm.submit()}
        confirmLoading={submitting}
      >
        <Form form={addItemForm} layout='vertical' onFinish={handleAddItem}>
          <Form.Item name='item_type' label='Type' rules={[{required: true}]}>
            <Select>
              <Option value='consultation'>Consultation</Option>
              <Option value='nursing'>Nursing</Option>
              <Option value='procedure'>Procedure</Option>
              <Option value='investigation'>Investigation</Option>
              <Option value='pharmacy'>Pharmacy</Option>
              <Option value='other'>Other</Option>
            </Select>
          </Form.Item>
          <Form.Item name='description' label='Description' rules={[{required: true, message: 'Description is required'}]}>
            <Input placeholder='e.g. Wound dressing' />
          </Form.Item>
          <Space wrap>
            <Form.Item name='quantity' label='Quantity' rules={[{required: true}]}>
              <InputNumber min={0.01} style={{width: 140}} />
            </Form.Item>
            <Form.Item name='unit_price' label='Unit Price' rules={[{required: true}]}>
              <InputNumber min={0} style={{width: 140}} />
            </Form.Item>
          </Space>
        </Form>
      </Modal>

      {/* ============= PAYMENT MODAL ============= */}
      <Modal
        title='Collect Payment'
        open={paymentModalOpen}
        onCancel={() => setPaymentModalOpen(false)}
        onOk={() => paymentForm.submit()}
        confirmLoading={submitting}
        width={640}
      >
        {bill && (
          <div className='alert alert-info mb-3'>
            Outstanding balance: <strong>{bill.balance}</strong>
          </div>
        )}
        <Form form={paymentForm} layout='vertical' onFinish={handleRecordPayment}>
          <Form.List name='payments'>
            {(fields, {add, remove}) => (
              <>
                {fields.map((field) => (
                  <Row gutter={8} key={field.key} align='middle'>
                    <Col span={7}>
                      <Form.Item
                        {...field}
                        name={[field.name, 'payment_method']}
                        label='Mode'
                        rules={[{required: true, message: 'Required'}]}
                      >
                        <Select>
                          <Option value='cash'>Cash</Option>
                          <Option value='card'>Card</Option>
                          <Option value='bank'>Bank Transfer</Option>
                          <Option value='mobile'>Mobile Banking</Option>
                          <Option value='insurance'>Insurance</Option>
                          <Option value='cheque'>Cheque</Option>
                          <Option value='other'>Other</Option>
                        </Select>
                      </Form.Item>
                    </Col>
                    <Col span={6}>
                      <Form.Item
                        {...field}
                        name={[field.name, 'amount']}
                        label='Amount'
                        rules={[{required: true, message: 'Required'}]}
                      >
                        <InputNumber min={0.01} style={{width: '100%'}} />
                      </Form.Item>
                    </Col>
                    <Col span={8}>
                      <Form.Item {...field} name={[field.name, 'reference_no']} label='Reference'>
                        <Input placeholder='Optional' />
                      </Form.Item>
                    </Col>
                    <Col span={3}>
                      {fields.length > 1 && (
                        <Button danger type='text' icon={<DeleteOutlined />} onClick={() => remove(field.name)} />
                      )}
                    </Col>
                  </Row>
                ))}
                <Button type='dashed' block icon={<PlusOutlined />} onClick={() => add({payment_method: 'cash'})}>
                  Add Payment Mode
                </Button>
              </>
            )}
          </Form.List>
        </Form>
      </Modal>

      {/* ============= DISCOUNT MODAL ============= */}
      <Modal
        title='Apply Discount'
        open={discountModalOpen}
        onCancel={() => setDiscountModalOpen(false)}
        onOk={() => discountForm.submit()}
        confirmLoading={submitting}
      >
        <Form form={discountForm} layout='vertical' onFinish={handleApplyDiscount} initialValues={{type: 'percent'}}>
          <Space wrap>
            <Form.Item name='amount' label='Amount' rules={[{required: true, message: 'Amount is required'}]}>
              <InputNumber min={0.01} style={{width: 160}} />
            </Form.Item>
            <Form.Item name='type' label='Type' rules={[{required: true}]}>
              <Select style={{width: 140}}>
                <Option value='percent'>Percent (%)</Option>
                <Option value='flat'>Flat Amount</Option>
              </Select>
            </Form.Item>
          </Space>
          <Form.Item name='reason' label='Reason'>
            <TextArea rows={2} placeholder='Why is this discount being given?' />
          </Form.Item>
          <div className='text-muted fs-7'>
            Discounts above 10% of the bill subtotal require supervisor approval before being applied.
          </div>
        </Form>
      </Modal>

      {/* ============= ADVANCE MODAL ============= */}
      <Modal
        title='Receive Advance Payment'
        open={advanceModalOpen}
        onCancel={() => setAdvanceModalOpen(false)}
        onOk={() => advanceForm.submit()}
        confirmLoading={submitting}
      >
        <Form form={advanceForm} layout='vertical' onFinish={handleReceiveAdvance}>
          <Form.Item name='amount' label='Amount' rules={[{required: true, message: 'Amount is required'}]}>
            <InputNumber min={0.01} style={{width: '100%'}} />
          </Form.Item>
          <Form.Item name='payment_method' label='Payment Method' rules={[{required: true}]}>
            <Select>
              <Option value='cash'>Cash</Option>
              <Option value='card'>Card</Option>
              <Option value='bank'>Bank Transfer</Option>
              <Option value='mobile'>Mobile Banking</Option>
              <Option value='cheque'>Cheque</Option>
              <Option value='other'>Other</Option>
            </Select>
          </Form.Item>
          <Form.Item name='reference_no' label='Reference'>
            <Input placeholder='Optional' />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>

      {/* ============= TRANSFER BED MODAL ============= */}
      <Modal
        title='Transfer Bed'
        open={transferModalOpen}
        onCancel={() => setTransferModalOpen(false)}
        onOk={() => transferForm.submit()}
        confirmLoading={submitting}
      >
        <Form form={transferForm} layout='vertical' onFinish={handleTransferBed}>
          <Form.Item name='ward_id' label='Ward' rules={[{required: true, message: 'Select a ward'}]}>
            <WardSelect
              placeholder='Select ward'
              onSelect={(value: any) => transferForm.setFieldsValue({ward_id: value, bed_id: null})}
              onChange={(value: any) => transferForm.setFieldsValue({ward_id: value, bed_id: null})}
            />
          </Form.Item>
          <Form.Item name='bed_id' label='Bed' rules={[{required: true, message: 'Select a bed'}]}>
            <Select
              placeholder={
                !transferWardId
                  ? 'Select ward first'
                  : loadingTransferBeds
                  ? 'Loading beds…'
                  : transferBeds.length
                  ? 'Select vacant bed'
                  : 'No vacant beds in this ward'
              }
              loading={loadingTransferBeds}
              disabled={!transferWardId}
            >
              {transferBeds.map((b: any) => (
                <Option key={b.id} value={b.id}>
                  {b.bed_number} {b.bed_type ? `(${b.bed_type})` : ''} — {b.daily_rate}/night
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='reason' label='Reason'>
            <TextArea rows={2} placeholder='Why is the patient being moved?' />
          </Form.Item>
        </Form>
      </Modal>

      {/* ============= EXIT (DISCHARGE) MODAL ============= */}
      <Modal
        title='Discharge / Exit Admission'
        open={exitModalOpen}
        onCancel={() => setExitModalOpen(false)}
        onOk={() => exitForm.submit()}
        confirmLoading={submitting}
      >
        <Form form={exitForm} layout='vertical' onFinish={handleExit}>
          <Form.Item name='status' label='Exit Type' rules={[{required: true}]}>
            <Select>
              <Option value='discharged'>Discharged</Option>
              <Option value='dama'>Discharged Against Medical Advice (DAMA)</Option>
              <Option value='deceased'>Deceased</Option>
            </Select>
          </Form.Item>
          <Form.Item name='remarks' label='Remarks'>
            <TextArea rows={2} />
          </Form.Item>
          <Form.Item
            name='override_reason'
            label='Override Reason'
            tooltip='Only required if the bill still has an outstanding balance — this will be recorded on the audit log.'
          >
            <TextArea rows={2} placeholder='Leave blank if the bill is fully settled' />
          </Form.Item>
          {bill && bill.balance > 0 && (
            <div className='alert alert-warning'>
              This bill has an outstanding balance of <strong>{bill.balance}</strong>. Discharge will be blocked
              unless you provide an override reason above.
            </div>
          )}
        </Form>
      </Modal>
    </div>
  )
}

export default React.memo(IpdAdmissionView)

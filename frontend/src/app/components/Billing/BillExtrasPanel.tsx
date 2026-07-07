import React, {FC, useEffect, useState} from 'react'
import {Button, Select, Table, Tag, Modal, Form, Input, InputNumber, AutoComplete, notification, Popconfirm} from 'antd'
import {PlusOutlined} from '@ant-design/icons'
import {
  BillingPackageApi,
  BillRefundApi,
  InsuranceClaimApi,
  InsuranceCompanyApi,
  InsuranceSchemeApi,
  OpdBillApi,
  IpdBillApi,
} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'

const {Option} = Select
const {TextArea} = Input

const REFUND_STATUS_COLOR: Record<string, string> = {
  pending_approval: 'gold',
  approved: 'blue',
  processed: 'green',
  rejected: 'red',
}

const CLAIM_STATUS_COLOR: Record<string, string> = {
  draft: 'default',
  submitted: 'blue',
  under_review: 'gold',
  approved: 'green',
  partially_approved: 'cyan',
  rejected: 'red',
  settled: 'purple',
}

interface BillExtrasPanelProps {
  billId: number
  patientId: number
  billableType: 'opd_bill' | 'ipd_bill'
  bill: any
  onChanged: () => void
}

const BillExtrasPanel: FC<BillExtrasPanelProps> = ({billId, patientId, billableType, bill, onChanged}) => {
  const {t} = useLang()

  // ---- Package ----
  const [packageModalOpen, setPackageModalOpen] = useState(false)
  const [packages, setPackages] = useState<any[]>([])
  const [selectedPackageId, setSelectedPackageId] = useState<any>(null)
  const [applyingPackage, setApplyingPackage] = useState(false)

  const openPackageModal = () => {
    setSelectedPackageId(null)
    BillingPackageApi.list({$filter: `package_type='${billableType === 'opd_bill' ? 'opd' : 'ipd'}' or package_type='both'`, $top: 50})
      .then((res: any) => setPackages(res?.data?.results || []))
      .catch(() => setPackages([]))
    setPackageModalOpen(true)
  }

  const handleApplyPackage = async () => {
    if (!selectedPackageId) return
    setApplyingPackage(true)
    try {
      const api = billableType === 'opd_bill' ? OpdBillApi : IpdBillApi
      await api.applyPackage(billId, {billing_package_id: selectedPackageId})
      notification.success({message: t('Package applied successfully')})
      setPackageModalOpen(false)
      onChanged()
    } catch (e: any) {
      notification.error({message: t('Failed to apply package'), description: e?.response?.data?.message})
    } finally {
      setApplyingPackage(false)
    }
  }

  // ---- Refund ----
  const [refunds, setRefunds] = useState<any[]>([])
  const [refundModalOpen, setRefundModalOpen] = useState(false)
  const [refundForm] = Form.useForm()
  const [refundSubmitting, setRefundSubmitting] = useState(false)

  const loadRefunds = () => {
    BillRefundApi.byBill(billableType, billId)
      .then((res: any) => setRefunds(res?.data?.data ?? res?.data ?? []))
      .catch(() => setRefunds([]))
  }

  const handleRequestRefund = () => {
    refundForm.validateFields().then(async (values) => {
      setRefundSubmitting(true)
      try {
        await BillRefundApi.request({billable_type: billableType, billable_id: billId, ...values})
        notification.success({message: t('Refund requested successfully')})
        setRefundModalOpen(false)
        loadRefunds()
        onChanged()
      } catch (e: any) {
        notification.error({message: t('Failed to request refund'), description: e?.response?.data?.message})
      } finally {
        setRefundSubmitting(false)
      }
    })
  }

  const handleApproveRefund = async (id: any) => {
    try {
      await BillRefundApi.approve(id)
      notification.success({message: t('Refund approved')})
      loadRefunds()
      onChanged()
    } catch (e: any) {
      notification.error({message: t('Failed to approve refund'), description: e?.response?.data?.message})
    }
  }

  const handleRejectRefund = async (id: any) => {
    try {
      await BillRefundApi.reject(id, {reason: 'Rejected'})
      notification.success({message: t('Refund rejected')})
      loadRefunds()
    } catch (e: any) {
      notification.error({message: t('Failed to reject refund')})
    }
  }

  // ---- Insurance Claim ----
  const [claims, setClaims] = useState<any[]>([])
  const [claimModalOpen, setClaimModalOpen] = useState(false)
  const [claimForm] = Form.useForm()
  const [claimSubmitting, setClaimSubmitting] = useState(false)
  const [companies, setCompanies] = useState<any[]>([])
  const [schemes, setSchemes] = useState<any[]>([])

  const loadClaims = () => {
    InsuranceClaimApi.byBill(billableType, billId)
      .then((res: any) => setClaims(res?.data?.data ?? res?.data ?? []))
      .catch(() => setClaims([]))
  }

  const openClaimModal = () => {
    claimForm.resetFields()
    setSchemes([])
    InsuranceCompanyApi.dropdown({status: 1})
      .then((res: any) => setCompanies(res?.data?.results || res?.data || []))
      .catch(() => setCompanies([]))
    claimForm.setFieldsValue({claimed_amount: bill?.total})
    setClaimModalOpen(true)
  }

  const handleCompanyChange = (companyId: any) => {
    claimForm.setFieldsValue({insurance_scheme_id: null})
    InsuranceSchemeApi.byCompany(companyId)
      .then((res: any) => setSchemes(res?.data?.data ?? res?.data ?? []))
      .catch(() => setSchemes([]))
  }

  const handleRaiseClaim = () => {
    claimForm.validateFields().then(async (values) => {
      setClaimSubmitting(true)
      try {
        await InsuranceClaimApi.create({
          patient_id: patientId,
          billable_type: billableType,
          billable_id: billId,
          ...values,
        })
        notification.success({message: t('Insurance claim raised successfully')})
        setClaimModalOpen(false)
        loadClaims()
      } catch (e: any) {
        notification.error({message: t('Failed to raise claim'), description: e?.response?.data?.message})
      } finally {
        setClaimSubmitting(false)
      }
    })
  }

  const handleSubmitClaim = async (id: any) => {
    try {
      await InsuranceClaimApi.submit(id)
      notification.success({message: t('Claim submitted')})
      loadClaims()
    } catch (e: any) {
      notification.error({message: t('Failed to submit claim')})
    }
  }

  useEffect(() => {
    loadRefunds()
    loadClaims()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [billId])

  const refundColumns = [
    {dataIndex: 'refund_no', key: 'refund_no', title: t('Refund No')},
    {dataIndex: 'amount', key: 'amount', title: t('Amount')},
    {dataIndex: 'reason', key: 'reason', title: t('Reason')},
    {
      dataIndex: 'refund_status_label',
      key: 'refund_status',
      title: t('Status'),
      render: (text: string, record: any) => <Tag color={REFUND_STATUS_COLOR[record.refund_status]}>{text}</Tag>,
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      render: (_: any, record: any) =>
        record.refund_status === 'pending_approval' ? (
          <>
            <Popconfirm title={t('Approve this refund?')} onConfirm={() => handleApproveRefund(record.id)}>
              <Button size='small' type='primary' className='me-2'>
                {t('Approve')}
              </Button>
            </Popconfirm>
            <Popconfirm title={t('Reject this refund?')} onConfirm={() => handleRejectRefund(record.id)}>
              <Button size='small' danger>
                {t('Reject')}
              </Button>
            </Popconfirm>
          </>
        ) : null,
    },
  ]

  const claimColumns = [
    {dataIndex: 'claim_no', key: 'claim_no', title: t('Claim No')},
    {dataIndex: 'insurance_company_name', key: 'insurance_company_name', title: t('Insurer')},
    {dataIndex: 'claimed_amount', key: 'claimed_amount', title: t('Claimed')},
    {dataIndex: 'approved_amount', key: 'approved_amount', title: t('Approved'), render: (v: any) => v ?? '-'},
    {
      dataIndex: 'claim_status_label',
      key: 'claim_status',
      title: t('Status'),
      render: (text: string, record: any) => <Tag color={CLAIM_STATUS_COLOR[record.claim_status]}>{text}</Tag>,
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      render: (_: any, record: any) =>
        record.claim_status === 'draft' ? (
          <Button size='small' type='primary' onClick={() => handleSubmitClaim(record.id)}>
            {t('Submit')}
          </Button>
        ) : null,
    },
  ]

  return (
    <div className='mt-6'>
      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>{t('Package')}</h5>
        {!bill?.billing_package_id && (
          <Button size='small' icon={<PlusOutlined />} onClick={openPackageModal}>
            {t('Apply Package')}
          </Button>
        )}
      </div>
      {bill?.billing_package_id ? (
        <Tag color='blue' className='mb-4'>
          {t('Package Applied')}
        </Tag>
      ) : (
        <div className='text-muted fs-8 mb-4'>{t('No package applied to this bill.')}</div>
      )}

      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>{t('Refunds')}</h5>
        {bill?.paid > 0 && (
          <Button size='small' icon={<PlusOutlined />} onClick={() => setRefundModalOpen(true)}>
            {t('Request Refund')}
          </Button>
        )}
      </div>
      <Table rowKey='id' size='small' className='mb-4' columns={refundColumns} dataSource={refunds} pagination={false} />

      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>{t('Insurance Claims (TPA)')}</h5>
        <Button size='small' icon={<PlusOutlined />} onClick={openClaimModal}>
          {t('Raise Claim')}
        </Button>
      </div>
      <Table rowKey='id' size='small' columns={claimColumns} dataSource={claims} pagination={false} />

      <Modal
        title={t('Apply Billing Package')}
        open={packageModalOpen}
        onCancel={() => setPackageModalOpen(false)}
        onOk={handleApplyPackage}
        confirmLoading={applyingPackage}
        destroyOnClose
      >
        <Select
          style={{width: '100%'}}
          placeholder={t('Select a package')}
          value={selectedPackageId}
          onChange={setSelectedPackageId}
        >
          {packages.map((p: any) => (
            <Option key={p.id} value={p.id}>
              {p.name} ({p.fixed_price})
            </Option>
          ))}
        </Select>
      </Modal>

      <Modal
        title={t('Request Refund')}
        open={refundModalOpen}
        onCancel={() => setRefundModalOpen(false)}
        onOk={handleRequestRefund}
        confirmLoading={refundSubmitting}
        destroyOnClose
      >
        <Form form={refundForm} layout='vertical'>
          <Form.Item name='amount' label={t('Amount')} rules={[{required: true}]}>
            <InputNumber min={0.01} max={bill?.paid} precision={2} style={{width: '100%'}} />
          </Form.Item>
          <Form.Item name='reason' label={t('Reason')} rules={[{required: true}]}>
            <TextArea rows={3} />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        title={t('Raise Insurance Claim')}
        open={claimModalOpen}
        onCancel={() => setClaimModalOpen(false)}
        onOk={handleRaiseClaim}
        confirmLoading={claimSubmitting}
        destroyOnClose
      >
        <Form form={claimForm} layout='vertical'>
          <Form.Item name='insurance_company_id' label={t('Insurance Company')} rules={[{required: true}]}>
            <Select placeholder={t('Select Insurance Company')} onChange={handleCompanyChange}>
              {companies.map((c: any) => (
                <Option key={c.id} value={c.id}>
                  {c.name || c.text}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='insurance_scheme_id' label={t('Scheme')}>
            <Select placeholder={t('Select Scheme (optional)')} allowClear>
              {schemes.map((s: any) => (
                <Option key={s.id} value={s.id}>
                  {s.name}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='policy_number' label={t('Policy Number')}>
            <Input />
          </Form.Item>
          <Form.Item name='claimed_amount' label={t('Claimed Amount')} rules={[{required: true}]}>
            <InputNumber min={0} precision={2} style={{width: '100%'}} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default BillExtrasPanel

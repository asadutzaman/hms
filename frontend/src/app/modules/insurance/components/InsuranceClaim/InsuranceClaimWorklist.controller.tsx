import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Col, Form, Input, InputNumber, Modal, Row, Select, Statistic, Tabs, Tag, Typography} from 'antd'
import download from 'downloadjs'
import {InsuranceClaimApi, InsuranceClaimSettlementApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography
const {Option} = Select

const statusColor: Record<string, string> = {
  draft: 'default',
  submitted: 'processing',
  under_review: 'gold',
  approved: 'blue',
  partially_approved: 'orange',
  rejected: 'red',
  settled: 'green',
}

const InsuranceClaimWorklistController: FC = () => {
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [tracking, setTracking] = useState<any>({status_counts: [], pending_claims: []})
  const [downloadingId, setDownloadingId] = useState<any>(null)
  const [statusModalOpen, setStatusModalOpen] = useState(false)
  const [settleModalOpen, setSettleModalOpen] = useState(false)
  const [activeClaim, setActiveClaim] = useState<any>(null)
  const [saving, setSaving] = useState(false)
  const [statusForm] = Form.useForm()
  const [settleForm] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    InsuranceClaimApi.list({$top: 100})
      .then((res: any) => setRows(res?.data?.results ?? res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
    InsuranceClaimApi.tracking()
      .then((res: any) => setTracking(res?.data ?? {status_counts: [], pending_claims: []}))
      .catch(() => setTracking({status_counts: [], pending_claims: []}))
  }

  useEffect(() => {
    loadData()
  }, [])

  const handleSubmitClaim = (id: any) => {
    InsuranceClaimApi.submit(id)
      .then(() => {
        Message.success('Claim submitted.')
        loadData()
      })
      .catch(() => Message.error('Failed to submit claim.'))
  }

  const handleDownloadPdf = (id: any) => {
    setDownloadingId(id)
    InsuranceClaimApi.downloadFormPdf(id)
      .then((res: any) => {
        download(new Blob([res.data]), `claim-${id}.pdf`, {type: 'application/pdf'})
      })
      .catch(() => Message.error('Could not download claim document.'))
      .finally(() => setDownloadingId(null))
  }

  const openStatusModal = (claim: any) => {
    setActiveClaim(claim)
    statusForm.resetFields()
    setStatusModalOpen(true)
  }

  const handleUpdateStatus = async () => {
    try {
      const values = await statusForm.validateFields()
      setSaving(true)
      await InsuranceClaimApi.updateStatus(activeClaim.id, values)
      Message.success('Claim status updated.')
      setStatusModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to update claim status.')
    } finally {
      setSaving(false)
    }
  }

  const openSettleModal = (claim: any) => {
    setActiveClaim(claim)
    settleForm.resetFields()
    setSettleModalOpen(true)
  }

  const handleSettle = async () => {
    try {
      const values = await settleForm.validateFields()
      setSaving(true)
      await InsuranceClaimSettlementApi.create({...values, insurance_claim_id: activeClaim.id})
      Message.success('Claim settled.')
      setSettleModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error(typeof err?.data === 'string' ? err.data : 'Failed to settle claim.')
    } finally {
      setSaving(false)
    }
  }

  const renderClaimsTable = (list: any[]) => (
    <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
      <thead>
        <tr>
          <th>Claim No.</th>
          <th>Patient</th>
          <th>Insurer</th>
          <th>Claimed</th>
          <th>Approved</th>
          <th>Status</th>
          <th>Days in Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        {list.length === 0 && (
          <tr>
            <td colSpan={8} align='center'>
              No claims found.
            </td>
          </tr>
        )}
        {list.map((row: any) => (
          <tr key={row.id}>
            <td>{row.claim_no}</td>
            <td>{row.patient_name || row.patient?.first_name || '-'}</td>
            <td>{row.insurance_company_name || row.insurance_company?.name || '-'}</td>
            <td>{row.claimed_amount}</td>
            <td>{row.approved_amount ?? '-'}</td>
            <td>
              <Tag color={statusColor[row.claim_status] || 'default'}>{(row.claim_status_label || row.claim_status || '').toString()}</Tag>
            </td>
            <td>{row.days_in_status !== undefined ? Math.round(row.days_in_status) : '-'}</td>
            <td>
              <div className='d-flex flex-wrap' style={{gap: 6}}>
                {row.claim_status === 'draft' && (
                  <Button size='small' onClick={() => handleSubmitClaim(row.id)}>
                    Submit
                  </Button>
                )}
                {['submitted', 'under_review'].includes(row.claim_status) && (
                  <Button size='small' onClick={() => openStatusModal(row)}>
                    Update Status
                  </Button>
                )}
                {['approved', 'partially_approved'].includes(row.claim_status) && (
                  <Button size='small' type='primary' onClick={() => openSettleModal(row)}>
                    Settle
                  </Button>
                )}
                <Button size='small' loading={downloadingId === row.id} onClick={() => handleDownloadPdf(row.id)}>
                  PDF
                </Button>
              </div>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  )

  return (
    <div className='p-6'>
      <Title level={3}>Insurance Claims</Title>

      <Row gutter={[16, 16]} className='mb-4'>
        {tracking.status_counts.map((s: any) => (
          <Col span={4} key={s.claim_status}>
            <Card size='small'>
              <Statistic title={<span className='text-capitalize'>{s.claim_status.replace(/_/g, ' ')}</span>} value={s.claim_count} />
            </Card>
          </Col>
        ))}
      </Row>

      <Card loading={loading}>
        <Tabs
          items={[
            {key: 'pending', label: 'Pending / Tracking', children: renderClaimsTable(tracking.pending_claims)},
            {key: 'all', label: 'All Claims', children: renderClaimsTable(rows)},
          ]}
        />
      </Card>

      <Modal title='Update Claim Status' open={statusModalOpen} onCancel={() => setStatusModalOpen(false)} onOk={handleUpdateStatus} confirmLoading={saving}>
        <Form form={statusForm} layout='vertical'>
          <Form.Item name='claim_status' label='Status' rules={[{required: true}]}>
            <Select
              options={[
                {value: 'under_review', label: 'Under Review'},
                {value: 'approved', label: 'Approved'},
                {value: 'partially_approved', label: 'Partially Approved'},
                {value: 'rejected', label: 'Rejected'},
              ]}
            />
          </Form.Item>
          <Form.Item name='approved_amount' label='Approved Amount'>
            <InputNumber className='w-100' min={0} />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>

      <Modal title='Settle Claim' open={settleModalOpen} onCancel={() => setSettleModalOpen(false)} onOk={handleSettle} confirmLoading={saving}>
        <p className='text-muted'>
          Claimed: {activeClaim?.claimed_amount} &nbsp; Approved: {activeClaim?.approved_amount ?? '-'}
        </p>
        <Form form={settleForm} layout='vertical'>
          <Form.Item name='bank_reference_no' label='Bank Reference No.' rules={[{required: true}]}>
            <Input placeholder='e.g. NEFT/UTR reference' />
          </Form.Item>
          <Form.Item name='bank_receipt_date' label='Bank Receipt Date' rules={[{required: true}]}>
            <Input type='date' />
          </Form.Item>
          <Form.Item name='settled_amount' label='Settled Amount' rules={[{required: true}]}>
            <InputNumber className='w-100' min={0} />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
        <p className='text-muted fs-8'>
          If the settled amount is less than the approved (or claimed) amount, the shortfall is automatically billed to the patient.
        </p>
      </Modal>
    </div>
  )
}

export default InsuranceClaimWorklistController

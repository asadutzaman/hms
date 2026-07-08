import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Modal, Spin, Tabs, Tag, Typography} from 'antd'
import download from 'downloadjs'
import {PatientPortalApi, PatientPortalPaymentApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title, Text} = Typography

const PatientBillsController: FC = () => {
  const [loading, setLoading] = useState<boolean>(true)
  const [downloadingId, setDownloadingId] = useState<string | null>(null)
  const [opdBills, setOpdBills] = useState<any[]>([])
  const [ipdBills, setIpdBills] = useState<any[]>([])
  const [payModalOpen, setPayModalOpen] = useState(false)
  const [activeBill, setActiveBill] = useState<{type: 'opd_bill' | 'ipd_bill'; id: number; balance: number} | null>(null)
  const [paying, setPaying] = useState(false)
  const [transactionRef, setTransactionRef] = useState<string | null>(null)

  const loadBills = () => {
    setLoading(true)
    Promise.all([PatientPortalApi.getOpdBills(), PatientPortalApi.getIpdBills()])
      .then(([opdRes, ipdRes]: any[]) => {
        setOpdBills(opdRes?.data || [])
        setIpdBills(ipdRes?.data || [])
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }

  useEffect(() => {
    loadBills()
  }, [])

  const handleDownload = (type: 'opd' | 'ipd', id: number) => {
    setDownloadingId(`${type}-${id}`)
    const request =
      type === 'opd' ? PatientPortalApi.downloadOpdBillPdf(id) : PatientPortalApi.downloadIpdBillPdf(id)
    request
      .then((res: any) => {
        download(new Blob([res.data]), `${type}-receipt-${id}.pdf`, {type: 'application/pdf'})
        setDownloadingId(null)
      })
      .catch(() => {
        Message.error('Could not download the receipt. Please try again.')
        setDownloadingId(null)
      })
  }

  const openPay = (type: 'opd' | 'ipd', row: any) => {
    setActiveBill({type: type === 'opd' ? 'opd_bill' : 'ipd_bill', id: row.id, balance: Number(row.balance)})
    setTransactionRef(null)
    setPayModalOpen(true)
  }

  const handleInitiate = () => {
    if (!activeBill) return
    setPaying(true)
    PatientPortalPaymentApi.initiate({payable_type: activeBill.type, payable_id: activeBill.id})
      .then((res: any) => {
        setTransactionRef(res?.data?.transaction_ref)
      })
      .catch((err: any) => Message.error(typeof err?.data === 'string' ? err.data : 'Could not start payment.'))
      .finally(() => setPaying(false))
  }

  const handleConfirm = (outcome: 'success' | 'failure') => {
    if (!transactionRef) return
    setPaying(true)
    PatientPortalPaymentApi.confirm(transactionRef, outcome)
      .then(() => {
        Message.success(outcome === 'success' ? 'Payment successful.' : 'Payment marked as failed.')
        setPayModalOpen(false)
        loadBills()
      })
      .catch(() => Message.error('Could not confirm payment.'))
      .finally(() => setPaying(false))
  }

  const renderTable = (rows: any[], type: 'opd' | 'ipd') => (
    <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
      <thead>
        <tr>
          <th>Serial No.</th>
          <th>Bill No.</th>
          <th>Date</th>
          <th>Total</th>
          <th>Paid</th>
          <th>Balance</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        {rows.length === 0 && (
          <tr>
            <td colSpan={8} align='center'>
              No bills found.
            </td>
          </tr>
        )}
        {rows.map((row: any, index: number) => (
          <tr key={row.id}>
            <td align='center'>{index + 1}</td>
            <td>{row.bill_no}</td>
            <td>{row.billed_at}</td>
            <td>{row.total}</td>
            <td>{row.paid}</td>
            <td>{row.balance}</td>
            <td className='text-capitalize'>{row.bill_status}</td>
            <td>
              <Button
                size='small'
                className='me-2'
                loading={downloadingId === `${type}-${row.id}`}
                onClick={() => handleDownload(type, row.id)}
              >
                Download PDF
              </Button>
              {Number(row.balance) > 0 && (
                <Button size='small' type='primary' onClick={() => openPay(type, row)}>
                  Pay Now
                </Button>
              )}
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  )

  return (
    <Spin spinning={loading}>
      <Title level={3}>Bills</Title>
      <Card>
        <Tabs
          items={[
            {key: 'opd', label: 'OPD Bills', children: renderTable(opdBills, 'opd')},
            {key: 'ipd', label: 'IPD Bills', children: renderTable(ipdBills, 'ipd')},
          ]}
        />
      </Card>

      <Modal title='Pay Outstanding Balance' open={payModalOpen} onCancel={() => setPayModalOpen(false)} footer={null}>
        <p>
          Amount due: <strong>{activeBill?.balance}</strong>
        </p>
        {!transactionRef ? (
          <Button type='primary' block loading={paying} onClick={handleInitiate}>
            Proceed to Payment
          </Button>
        ) : (
          <div>
            <Text type='secondary'>
              Transaction <Tag>{transactionRef}</Tag> initiated. This is a demo payment gateway — choose an outcome to
              simulate the checkout result.
            </Text>
            <div className='d-flex mt-4' style={{gap: 8}}>
              <Button type='primary' block loading={paying} onClick={() => handleConfirm('success')}>
                Simulate Successful Payment
              </Button>
              <Button danger block loading={paying} onClick={() => handleConfirm('failure')}>
                Simulate Failed Payment
              </Button>
            </div>
          </div>
        )}
      </Modal>
    </Spin>
  )
}

export default PatientBillsController

import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Spin, Tabs, Typography} from 'antd'
import download from 'downloadjs'
import {PatientPortalApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography

const PatientBillsController: FC = () => {
  const [loading, setLoading] = useState<boolean>(true)
  const [downloadingId, setDownloadingId] = useState<string | null>(null)
  const [opdBills, setOpdBills] = useState<any[]>([])
  const [ipdBills, setIpdBills] = useState<any[]>([])

  useEffect(() => {
    Promise.all([PatientPortalApi.getOpdBills(), PatientPortalApi.getIpdBills()])
      .then(([opdRes, ipdRes]: any[]) => {
        setOpdBills(opdRes?.data || [])
        setIpdBills(ipdRes?.data || [])
        setLoading(false)
      })
      .catch(() => setLoading(false))
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
                loading={downloadingId === `${type}-${row.id}`}
                onClick={() => handleDownload(type, row.id)}
              >
                Download PDF
              </Button>
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
    </Spin>
  )
}

export default PatientBillsController

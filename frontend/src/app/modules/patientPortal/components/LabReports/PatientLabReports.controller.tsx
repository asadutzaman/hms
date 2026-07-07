import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Spin, Tag, Typography} from 'antd'
import download from 'downloadjs'
import {PatientPortalApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography

const PatientLabReportsController: FC = () => {
  const [loading, setLoading] = useState<boolean>(true)
  const [downloadingId, setDownloadingId] = useState<number | null>(null)
  const [rows, setRows] = useState<any[]>([])

  useEffect(() => {
    PatientPortalApi.getLabReports()
      .then((res: any) => {
        setRows(res?.data || [])
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }, [])

  const handleDownload = (id: number) => {
    setDownloadingId(id)
    PatientPortalApi.downloadLabReportPdf(id)
      .then((res: any) => {
        download(new Blob([res.data]), `lab-report-${id}.pdf`, {type: 'application/pdf'})
        setDownloadingId(null)
      })
      .catch(() => {
        Message.error('This report is not available for download yet (results may still be pending verification).')
        setDownloadingId(null)
      })
  }

  return (
    <Spin spinning={loading}>
      <Title level={3}>Lab Reports</Title>
      <Card>
        <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
          <thead>
            <tr>
              <th>Serial No.</th>
              <th>Order No.</th>
              <th>Ordered Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 && (
              <tr>
                <td colSpan={5} align='center'>
                  No lab orders found.
                </td>
              </tr>
            )}
            {rows.map((row: any, index: number) => (
              <tr key={row.id}>
                <td align='center'>{index + 1}</td>
                <td>{row.order_no || row.id}</td>
                <td>{row.ordered_at}</td>
                <td>
                  <Tag color={row.status === 'reported' || row.status === 'verified' ? 'success' : 'default'}>
                    {row.status}
                  </Tag>
                </td>
                <td>
                  <Button
                    size='small'
                    loading={downloadingId === row.id}
                    onClick={() => handleDownload(row.id)}
                  >
                    Download PDF
                  </Button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>
    </Spin>
  )
}

export default PatientLabReportsController

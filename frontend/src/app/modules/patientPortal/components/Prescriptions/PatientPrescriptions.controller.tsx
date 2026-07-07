import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Spin, Typography} from 'antd'
import download from 'downloadjs'
import {PatientPortalApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography

const PatientPrescriptionsController: FC = () => {
  const [loading, setLoading] = useState<boolean>(true)
  const [downloadingId, setDownloadingId] = useState<number | null>(null)
  const [rows, setRows] = useState<any[]>([])

  useEffect(() => {
    PatientPortalApi.getPrescriptions()
      .then((res: any) => {
        setRows(res?.data || [])
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }, [])

  const handleDownload = (id: number) => {
    setDownloadingId(id)
    PatientPortalApi.downloadPrescriptionPdf(id)
      .then((res: any) => {
        download(new Blob([res.data]), `prescription-${id}.pdf`, {type: 'application/pdf'})
        setDownloadingId(null)
      })
      .catch(() => {
        Message.error('Could not download the prescription. Please try again.')
        setDownloadingId(null)
      })
  }

  return (
    <Spin spinning={loading}>
      <Title level={3}>Prescriptions</Title>
      <Card>
        <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
          <thead>
            <tr>
              <th>Serial No.</th>
              <th>Date</th>
              <th>Doctor</th>
              <th>Diagnosis</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 && (
              <tr>
                <td colSpan={5} align='center'>
                  No prescriptions found.
                </td>
              </tr>
            )}
            {rows.map((row: any, index: number) => (
              <tr key={row.id}>
                <td align='center'>{index + 1}</td>
                <td>{row.prescribed_at || row.created_at}</td>
                <td>{row.doctor_name || row.doctor?.name_en || '-'}</td>
                <td>{row.diagnosis || '-'}</td>
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

export default PatientPrescriptionsController

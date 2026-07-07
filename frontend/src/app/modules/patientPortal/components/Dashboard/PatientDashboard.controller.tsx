import React, {FC, useEffect, useState} from 'react'
import {Card, Col, Row, Spin, Typography} from 'antd'
import {Link} from 'react-router-dom'
import {PatientPortalApi} from 'src/app/api'

const {Title} = Typography

const quickLinks = [
  {to: '/patient-portal/appointments', label: 'Appointments', desc: 'Book or manage your appointments'},
  {to: '/patient-portal/prescriptions', label: 'Prescriptions', desc: 'View and download prescriptions'},
  {to: '/patient-portal/lab-reports', label: 'Lab Reports', desc: 'View and download lab reports'},
  {to: '/patient-portal/bills', label: 'Bills', desc: 'View and download invoices/receipts'},
  {to: '/patient-portal/timeline', label: 'History', desc: 'Your full visit timeline'},
]

const PatientDashboardController: FC = () => {
  const [loading, setLoading] = useState<boolean>(true)
  const [patient, setPatient] = useState<any>(null)

  useEffect(() => {
    PatientPortalApi.me()
      .then((res: any) => {
        setPatient(res?.data || null)
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }, [])

  return (
    <Spin spinning={loading}>
      <Title level={3}>Welcome{patient?.first_name ? `, ${patient.first_name}` : ''}</Title>
      {patient?.mrn && <p className='text-muted'>MRN: {patient.mrn}</p>}

      <Row gutter={[16, 16]} className='mt-4'>
        {quickLinks.map((link) => (
          <Col span={8} key={link.to}>
            <Link to={link.to}>
              <Card hoverable>
                <Title level={5}>{link.label}</Title>
                <p className='text-muted mb-0'>{link.desc}</p>
              </Card>
            </Link>
          </Col>
        ))}
      </Row>
    </Spin>
  )
}

export default PatientDashboardController

import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Col, Row, Space, Statistic, Table, Tag, Spin} from 'antd'
import {
  TeamOutlined,
  ClockCircleOutlined,
  CheckCircleOutlined,
  HourglassOutlined,
  RightCircleOutlined,
  EyeOutlined,
} from '@ant-design/icons'
import {useNavigate} from 'react-router-dom'
import dayjs from 'dayjs'
import {DoctorPortalApi, OpdVisitApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {useLang} from 'src/app/hooks/useLang'

const queueStatusColor: Record<string, string> = {
  waiting: 'blue',
  vitals_taken: 'cyan',
  in_consultation: 'gold',
}

const appointmentStatusColor: Record<string, string> = {
  confirmed: 'blue',
  checked_in: 'cyan',
  in_consultation: 'gold',
  completed: 'green',
  cancelled: 'red',
}

const patientName = (patient: any) =>
  patient?.full_name ?? [patient?.first_name, patient?.last_name].filter(Boolean).join(' ')

const DoctorDashboardController: FC = () => {
  const [loading, setLoading] = useState(false)
  const [startingVisitId, setStartingVisitId] = useState<any>(null)
  const [data, setData] = useState<any>({
    today_appointment_count: 0,
    queue_count: 0,
    queue: [],
    stats: {},
    week_trend: [],
    appointments: [],
  })
  const {handleErrorMessage} = useErrorHandler()
  const {t} = useLang()
  const navigate = useNavigate()

  const loadData = () => {
    setLoading(true)
    DoctorPortalApi.dashboard()
      .then((res: any) => {
        setData(res?.data?.data ?? res?.data ?? {})
        setLoading(false)
      })
      .catch((err) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const openVisit = (visitId: any) => navigate(`/admin/opd/view/${visitId}`)

  const startConsultation = (visitId: any) => {
    setStartingVisitId(visitId)
    OpdVisitApi.transition(visitId, {to_status: 'in_consultation'})
      .then(() => openVisit(visitId))
      .catch((err) => handleErrorMessage(err))
      .finally(() => setStartingVisitId(null))
  }

  const queueColumns = [
    {title: t('Token'), dataIndex: 'token_number', width: 80},
    {
      title: t('Patient'),
      key: 'patient',
      render: (_: any, record: any) => patientName(record.patient),
    },
    {
      title: t('Status'),
      dataIndex: 'status',
      render: (status: string) => (
        <Tag color={queueStatusColor[status] || 'default'}>{status}</Tag>
      ),
    },
    {
      title: t('Actions'),
      key: 'actions',
      width: 240,
      render: (_: any, record: any) => (
        <Space>
          <Button size='small' icon={<EyeOutlined />} onClick={() => openVisit(record.id)}>
            {t('Open')}
          </Button>
          {['waiting', 'vitals_taken'].includes(record.status) && (
            <Button
              size='small'
              type='primary'
              icon={<RightCircleOutlined />}
              loading={startingVisitId === record.id}
              onClick={() => startConsultation(record.id)}
            >
              {t('Start Consultation')}
            </Button>
          )}
        </Space>
      ),
    },
  ]

  const appointmentColumns = [
    {title: t('Token'), dataIndex: 'token_number', width: 80},
    {title: t('Appointment No'), dataIndex: 'appointment_no'},
    {title: t('Patient'), dataIndex: 'patient_name'},
    {title: t('Time'), dataIndex: 'appointment_time'},
    {
      title: t('Status'),
      dataIndex: 'status',
      render: (status: string) => (
        <Tag color={appointmentStatusColor[status] || 'default'}>{status}</Tag>
      ),
    },
  ]

  const stats = data.stats || {}
  const weekTrend: any[] = data.week_trend || []
  const maxTrend = Math.max(1, ...weekTrend.map((d: any) => d.count || 0))

  return (
    <div className='p-6'>
      <Spin spinning={loading}>
        <Row gutter={16} className='mb-6'>
          <Col span={6}>
            <Card>
              <Statistic
                title={t("Today's Appointments")}
                value={data.today_appointment_count}
                prefix={<TeamOutlined />}
              />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic
                title={t('Current Queue')}
                value={data.queue_count}
                prefix={<ClockCircleOutlined />}
              />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic
                title={t('Seen Today')}
                value={stats.seen ?? 0}
                prefix={<CheckCircleOutlined />}
                valueStyle={{color: '#3f8600'}}
              />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic
                title={t('Pending')}
                value={stats.pending ?? 0}
                prefix={<HourglassOutlined />}
                valueStyle={{color: '#cf1322'}}
              />
            </Card>
          </Col>
        </Row>

        <Card title={t('Waiting Queue')} className='mb-6'>
          <Table
            rowKey='id'
            columns={queueColumns}
            dataSource={data.queue || []}
            pagination={false}
          />
        </Card>

        <Row gutter={16}>
          <Col span={14}>
            <Card title={t("Today's Appointments")}>
              <Table
                rowKey='id'
                columns={appointmentColumns}
                dataSource={data.appointments || []}
                pagination={false}
                size='small'
              />
            </Card>
          </Col>
          <Col span={10}>
            <Card title={t('Visits — Last 7 Days')}>
              <div className='d-flex justify-content-between align-items-end' style={{minHeight: 120}}>
                {weekTrend.map((day: any) => (
                  <div key={day.date} className='text-center' style={{flex: 1}}>
                    <div className='text-muted mb-1'>{day.count}</div>
                    <div
                      className='mx-auto rounded'
                      style={{
                        width: 18,
                        height: Math.max(4, Math.round((day.count / maxTrend) * 70)),
                        background: dayjs(day.date).isSame(dayjs(), 'day') ? '#1677ff' : '#d9d9d9',
                      }}
                    />
                    <div className='text-muted mt-1' style={{fontSize: 11}}>
                      {dayjs(day.date).format('dd')}
                    </div>
                  </div>
                ))}
              </div>
            </Card>
          </Col>
        </Row>
      </Spin>
    </div>
  )
}

export default DoctorDashboardController

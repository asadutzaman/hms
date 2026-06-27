import React, {FC, useEffect, useState, useCallback} from 'react'
import {
  Card,
  Col,
  Row,
  Badge,
  Button,
  Space,
  Statistic,
  Empty,
  notification,
  Select,
  Tag,
  Tooltip,
} from 'antd'
import {
  ClockCircleOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  UserOutlined,
  ReloadOutlined,
  PlayCircleOutlined,
  StopOutlined,
  CalendarOutlined,
} from '@ant-design/icons'
import {AppointmentApi} from 'src/app/api'
import {HttpService} from 'src/app/services/http.services'
import {CONSTANT_CONFIG} from 'src/app/constants'
import {DateTimeUtils} from 'src/app/utils'

const {Option} = Select

const statusColors: any = {
  scheduled: 'blue',
  confirmed: 'cyan',
  checked_in: 'orange',
  in_consultation: 'gold',
  completed: 'green',
  cancelled: 'red',
  no_show: 'volcano',
  rescheduled: 'purple',
}

const QueueController: FC<any> = () => {
  const [loading, setLoading] = useState(false)
  const [appointments, setAppointments] = useState<any[]>([])
  const [filterDate, setFilterDate] = useState<string>(
    new Date().toISOString().slice(0, 10)
  )
  const [filterDoctorId, setFilterDoctorId] = useState<string>('')
  const [filterDepartmentId, setFilterDepartmentId] = useState<string>('')
  const [filterStatus, setFilterStatus] = useState<string>('active')
  const [actingId, setActingId] = useState<number | null>(null)
  const [autoRefresh, setAutoRefresh] = useState(false)

  const fetchQueue = useCallback(async () => {
    setLoading(true)
    try {
      const response: any = await AppointmentApi.queue({
        appointment_date: filterDate,
        doctor_id: filterDoctorId || undefined,
        department_id: filterDepartmentId || undefined,
        status: filterStatus,
      })
      const data =
        response?.data?.data?.appointments ||
        response?.data?.data ||
        response?.data ||
        []
      setAppointments(Array.isArray(data) ? data : [])
    } catch (e) {
      console.error('Failed to load queue', e)
      setAppointments([])
    } finally {
      setLoading(false)
    }
  }, [filterDate, filterDoctorId, filterDepartmentId, filterStatus])

  useEffect(() => {
    fetchQueue()
  }, [fetchQueue])

  useEffect(() => {
    if (!autoRefresh) return
    const interval = setInterval(fetchQueue, 30000) // 30s
    return () => clearInterval(interval)
  }, [autoRefresh, fetchQueue])

  const actionCall = async (id: number, action: string) => {
    setActingId(id)
    try {
      const url = `${CONSTANT_CONFIG.SERVER_PREFIX}/appointment/${id}/${action}`
      await HttpService.post(url, {}, {}, {})
      notification.success({
        message: 'Action successful',
        description: `Appointment ${action.replace('_', ' ')}d`,
      })
      await fetchQueue()
    } catch (e: any) {
      notification.error({
        message: 'Action failed',
        description:
          e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setActingId(null)
    }
  }

  // Group appointments by status for columns
  const grouped = {
    waiting: appointments.filter(
      (a) => a.status === 'checked_in' || a.status === 'confirmed'
    ),
    in_consultation: appointments.filter((a) => a.status === 'in_consultation'),
    scheduled: appointments.filter((a) => a.status === 'scheduled'),
  }

  const stats = {
    total: appointments.length,
    waiting: grouped.waiting.length,
    in_consultation: grouped.in_consultation.length,
    scheduled: grouped.scheduled.length,
    completed: appointments.filter((a) => a.status === 'completed').length,
    no_show: appointments.filter((a) => a.status === 'no_show').length,
  }

  const renderCard = (appt: any) => (
    <Card
      key={appt.id}
      size='small'
      className='mb-2'
      loading={actingId === appt.id}
    >
      <Row gutter={8} align='middle'>
        <Col span={4}>
          <Statistic
            valueStyle={{fontSize: 24}}
            value={appt.token_number || '-'}
            suffix='#'
          />
        </Col>
        <Col span={14}>
          <div className='fw-bold'>
            {appt.patient?.first_name} {appt.patient?.last_name}
          </div>
          <div className='text-muted small'>
            <UserOutlined /> Dr. {appt.doctor?.name || appt.doctor_name || '-'}
            {appt.department?.name && (
              <span> · {appt.department.name}</span>
            )}
          </div>
          <div className='text-muted small'>
            <ClockCircleOutlined />{' '}
            {appt.start_time} - {appt.end_time}
          </div>
          {appt.reason && (
            <Tag color='default' className='mt-1'>
              {appt.reason}
            </Tag>
          )}
        </Col>
        <Col span={6} className='text-end'>
          <Tag color={statusColors[appt.status] || 'default'}>
            {appt.status}
          </Tag>
        </Col>
      </Row>

      <div className='mt-2 d-flex gap-1 flex-wrap'>
        {appt.status === 'checked_in' && (
          <Tooltip title='Start consultation'>
            <Button
              size='small'
              type='primary'
              icon={<PlayCircleOutlined />}
              onClick={() => actionCall(appt.id, 'start-consultation')}
            >
              Start
            </Button>
          </Tooltip>
        )}
        {appt.status === 'in_consultation' && (
          <Tooltip title='Mark complete'>
            <Button
              size='small'
              type='primary'
              icon={<CheckCircleOutlined />}
              onClick={() => actionCall(appt.id, 'complete')}
            >
              Complete
            </Button>
          </Tooltip>
        )}
        {['scheduled', 'confirmed', 'checked_in'].includes(appt.status) && (
          <Tooltip title='No show'>
            <Button
              size='small'
              danger
              icon={<CloseCircleOutlined />}
              onClick={() => actionCall(appt.id, 'mark-no-show')}
            >
              No Show
            </Button>
          </Tooltip>
        )}
        {['scheduled', 'confirmed', 'checked_in'].includes(appt.status) && (
          <Tooltip title='Cancel appointment'>
            <Button
              size='small'
              icon={<StopOutlined />}
              onClick={() => actionCall(appt.id, 'cancel')}
            >
              Cancel
            </Button>
          </Tooltip>
        )}
      </div>
    </Card>
  )

  const renderColumn = (title: string, list: any[], color: string) => (
    <Col span={8}>
      <Card
        title={
          <span>
            <Badge color={color} /> {title} <Tag>{list.length}</Tag>
          </span>
        }
        size='small'
        className='h-100'
      >
        {list.length === 0 ? (
          <Empty description={`No ${title.toLowerCase()}`} />
        ) : (
          list.map(renderCard)
        )}
      </Card>
    </Col>
  )

  return (
    <div className='card p-3'>
      <div className='d-flex justify-content-between align-items-center mb-3'>
        <div>
          <h2 className='mb-1'>Queue Board</h2>
          <p className='text-muted mb-0'>
            Live view of today's appointments by status
          </p>
        </div>
        <Space>
          <Select
            value={filterDate}
            onChange={setFilterDate}
            style={{width: 140}}
            placeholder='Date'
          >
            <Option value={new Date().toISOString().slice(0, 10)}>Today</Option>
            <Option
              value={
                new Date(Date.now() + 86400000).toISOString().slice(0, 10)
              }
            >
              Tomorrow
            </Option>
          </Select>
          <Select
            allowClear
            value={filterDoctorId || undefined}
            onChange={(v) => setFilterDoctorId(v || '')}
            style={{width: 140}}
            placeholder='Doctor'
          >
            {/* Populated dynamically */}
          </Select>
          <Select
            value={filterStatus}
            onChange={setFilterStatus}
            style={{width: 130}}
          >
            <Option value='active'>Active</Option>
            <Option value='scheduled'>Scheduled</Option>
            <Option value='confirmed'>Confirmed</Option>
            <Option value='checked_in'>Checked In</Option>
            <Option value='in_consultation'>In Consultation</Option>
            <Option value='completed'>Completed</Option>
          </Select>
          <Button
            icon={<ReloadOutlined />}
            onClick={fetchQueue}
            loading={loading}
          >
            Refresh
          </Button>
          <Button
            type={autoRefresh ? 'primary' : 'default'}
            onClick={() => setAutoRefresh(!autoRefresh)}
          >
            {autoRefresh ? 'Auto-refresh ON' : 'Auto-refresh'}
          </Button>
        </Space>
      </div>

      <Row gutter={16} className='mb-3'>
        <Col span={4}>
          <Card>
            <Statistic
              title='Total'
              value={stats.total}
              prefix={<CalendarOutlined />}
            />
          </Card>
        </Col>
        <Col span={4}>
          <Card>
            <Statistic
              title='Waiting'
              value={stats.waiting}
              valueStyle={{color: '#fa8c16'}}
              prefix={<ClockCircleOutlined />}
            />
          </Card>
        </Col>
        <Col span={4}>
          <Card>
            <Statistic
              title='In Consultation'
              value={stats.in_consultation}
              valueStyle={{color: '#faad14'}}
              prefix={<UserOutlined />}
            />
          </Card>
        </Col>
        <Col span={4}>
          <Card>
            <Statistic
              title='Scheduled'
              value={stats.scheduled}
              valueStyle={{color: '#1890ff'}}
            />
          </Card>
        </Col>
        <Col span={4}>
          <Card>
            <Statistic
              title='Completed'
              value={stats.completed}
              valueStyle={{color: '#52c41a'}}
              prefix={<CheckCircleOutlined />}
            />
          </Card>
        </Col>
        <Col span={4}>
          <Card>
            <Statistic
              title='No Show'
              value={stats.no_show}
              valueStyle={{color: '#ff4d4f'}}
              prefix={<CloseCircleOutlined />}
            />
          </Card>
        </Col>
      </Row>

      <Row gutter={16}>
        {renderColumn('Waiting', grouped.waiting, 'orange')}
        {renderColumn('In Consultation', grouped.in_consultation, 'gold')}
        {renderColumn('Scheduled', grouped.scheduled, 'blue')}
      </Row>
    </div>
  )
}

export default QueueController

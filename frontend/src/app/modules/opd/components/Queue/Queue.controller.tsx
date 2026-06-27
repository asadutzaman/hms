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
import {OpdVisitApi} from 'src/app/api'
import {HttpService} from 'src/app/services/http.services'
import {CONSTANT_CONFIG} from 'src/app/constants'
import {DateTimeUtils} from 'src/app/utils'

const {Option} = Select

const statusColors: any = {
  waiting: 'blue',
  vitals_taken: 'cyan',
  in_consultation: 'gold',
  completed: 'green',
  billed: 'purple',
  closed: 'grey',
  cancelled: 'red',
}

const QueueController: FC<any> = () => {
  const [loading, setLoading] = useState(false)
  const [OpdVisits, setOpdVisits] = useState<any[]>([])
  const [filterDate, setFilterDate] = useState<string>(
    new Date().toISOString().slice(0, 10)
  )
  const [filterDoctorId, setFilterDoctorId] = useState<string>('')
  const [filterDepartmentId, setFilterDepartmentId] = useState<string>('')
  const [actingId, setActingId] = useState<number | null>(null)
  const [autoRefresh, setAutoRefresh] = useState(false)

  const fetchQueue = useCallback(async () => {
    setLoading(true)
    try {
      const response: any = await OpdVisitApi.today({
        date: filterDate,
      })
      const data = response?.data?.visits || []
      setOpdVisits(Array.isArray(data) ? data : [])
    } catch (e) {
      console.error('Failed to load queue', e)
      setOpdVisits([])
    } finally {
      setLoading(false)
    }
  }, [filterDate])

  useEffect(() => {
    fetchQueue()
  }, [fetchQueue])

  useEffect(() => {
    if (!autoRefresh) return
    const interval = setInterval(fetchQueue, 30000) // 30s
    return () => clearInterval(interval)
  }, [autoRefresh, fetchQueue])

  const actionCall = async (id: number, toStatus: string) => {
    setActingId(id)
    try {
      await OpdVisitApi.transition(id, {
        to_status: toStatus,
      })
      notification.success({
        message: 'Action successful',
        description: `Visit status updated to ${toStatus.replace('_', ' ')}`,
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

  // Group OpdVisits by status for columns
  const grouped = {
    waiting: OpdVisits.filter((a) => a.status === 'waiting'),
    vitals_taken: OpdVisits.filter((a) => a.status === 'vitals_taken'),
    in_consultation: OpdVisits.filter((a) => a.status === 'in_consultation'),
  }

  const stats = {
    total: OpdVisits.length,
    waiting: grouped.waiting.length,
    vitals_taken: grouped.vitals_taken.length,
    in_consultation: grouped.in_consultation.length,
    completed: OpdVisits.filter((a) => a.status === 'completed').length,
    billed: OpdVisits.filter((a) => a.status === 'billed').length,
    cancelled: OpdVisits.filter((a) => a.status === 'cancelled').length,
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
            OPD No: {appt.opd_no}
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
        {appt.status === 'waiting' && (
          <Tooltip title='Mark vitals taken'>
            <Button
              size='small'
              type='primary'
              onClick={() => actionCall(appt.id, 'vitals_taken')}
            >
              Vitals Taken
            </Button>
          </Tooltip>
        )}
        {['waiting', 'vitals_taken'].includes(appt.status) && (
          <Tooltip title='Start consultation'>
            <Button
              size='small'
              type='primary'
              icon={<PlayCircleOutlined />}
              onClick={() => actionCall(appt.id, 'in_consultation')}
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
              onClick={() => actionCall(appt.id, 'completed')}
            >
              Complete
            </Button>
          </Tooltip>
        )}
        {!['completed', 'billed', 'closed', 'cancelled'].includes(appt.status) && (
          <Tooltip title='Cancel Visit'>
            <Button
              size='small'
              danger
              icon={<StopOutlined />}
              onClick={() => OpdVisitApi.cancel(appt.id, {cancellation_reason: 'Cancelled from queue'})}
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
            Live view of today's OpdVisits by status
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
              valueStyle={{color: '#1890ff'}}
              prefix={<ClockCircleOutlined />}
            />
          </Card>
        </Col>
        <Col span={4}>
          <Card>
            <Statistic
              title='Vitals Taken'
              value={stats.vitals_taken}
              valueStyle={{color: '#13c2c2'}}
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
              title='Cancelled'
              value={stats.cancelled}
              valueStyle={{color: '#ff4d4f'}}
              prefix={<CloseCircleOutlined />}
            />
          </Card>
        </Col>
      </Row>

      <Row gutter={16}>
        {renderColumn('Waiting', grouped.waiting, 'blue')}
        {renderColumn('Vitals Taken', grouped.vitals_taken, 'cyan')}
        {renderColumn('In Consultation', grouped.in_consultation, 'gold')}
      </Row>
    </div>
  )
}

export default QueueController

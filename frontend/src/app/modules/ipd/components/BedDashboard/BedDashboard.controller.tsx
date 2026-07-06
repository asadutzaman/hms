import React, {FC, useEffect, useState} from 'react'
import {Card, Col, Row, Statistic, Spin, Tag, Select, Button, Empty, notification} from 'antd'
import {
  ReloadOutlined,
  HomeOutlined,
  UserOutlined,
  ClockCircleOutlined,
  ToolOutlined,
  BookOutlined,
} from '@ant-design/icons'
import {BedApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {useLang} from 'src/app/hooks/useLang'

const {Option} = Select

const STATUS_META: Record<string, {label: string; color: string; icon: React.ReactNode}> = {
  vacant: {label: 'Vacant', color: 'green', icon: <HomeOutlined />},
  occupied: {label: 'Occupied', color: 'blue', icon: <UserOutlined />},
  reserved: {label: 'Reserved', color: 'gold', icon: <ClockCircleOutlined />},
  cleaning: {label: 'Cleaning', color: 'cyan', icon: <ClockCircleOutlined />},
  maintenance: {label: 'Maintenance', color: 'red', icon: <ToolOutlined />},
}

// A bed's status is only ever hand-set to one of these — 'occupied' is exclusively
// driven by the admission/discharge/transfer workflow, never picked manually here.
const MANUAL_STATUSES = ['vacant', 'reserved', 'cleaning', 'maintenance']

const BedDashboardController: FC = () => {
  const {t} = useLang()
  const {handleErrorMessage} = useErrorHandler()
  const [loading, setLoading] = useState(false)
  const [summary, setSummary] = useState<any>({total: 0, vacant: 0, occupied: 0, reserved: 0, cleaning: 0, maintenance: 0})
  const [wards, setWards] = useState<any[]>([])
  const [beds, setBeds] = useState<any[]>([])
  const [updatingBedId, setUpdatingBedId] = useState<number | null>(null)

  const loadData = () => {
    setLoading(true)
    Promise.all([BedApi.dashboard(), BedApi.board()])
      .then(([dashboardRes, boardRes]: any) => {
        const dashboardData = dashboardRes?.data?.data ?? dashboardRes?.data ?? {}
        setSummary(dashboardData.summary || {})
        setWards(dashboardData.wards || [])
        setBeds(boardRes?.data?.data ?? boardRes?.data ?? [])
      })
      .catch((err) => handleErrorMessage(err))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const handleStatusChange = async (bedId: number, bedStatus: string) => {
    setUpdatingBedId(bedId)
    try {
      await BedApi.updatePartial(bedId, {bed_status: bedStatus})
      notification.success({message: t('Bed status updated')})
      loadData()
    } catch (err: any) {
      handleErrorMessage(err?.response || err)
    } finally {
      setUpdatingBedId(null)
    }
  }

  const bedsByWard: Record<string, any[]> = {}
  beds.forEach((bed) => {
    const key = bed.ward_name || t('Unassigned')
    if (!bedsByWard[key]) bedsByWard[key] = []
    bedsByWard[key].push(bed)
  })

  const summaryTiles = [
    {key: 'total', label: t('Total Beds'), color: 'default', icon: <BookOutlined />},
    {key: 'vacant', label: t('Vacant'), color: STATUS_META.vacant.color, icon: STATUS_META.vacant.icon},
    {key: 'occupied', label: t('Occupied'), color: STATUS_META.occupied.color, icon: STATUS_META.occupied.icon},
    {key: 'reserved', label: t('Reserved'), color: STATUS_META.reserved.color, icon: STATUS_META.reserved.icon},
    {key: 'cleaning', label: t('Cleaning'), color: STATUS_META.cleaning.color, icon: STATUS_META.cleaning.icon},
    {key: 'maintenance', label: t('Maintenance'), color: STATUS_META.maintenance.color, icon: STATUS_META.maintenance.icon},
  ]

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <h3 className='mb-0'>{t('Bed Management Dashboard')}</h3>
        <Button icon={<ReloadOutlined />} onClick={loadData} loading={loading}>
          {t('Refresh')}
        </Button>
      </div>

      <Spin spinning={loading}>
        <Row gutter={16} className='mb-6'>
          {summaryTiles.map((tile) => (
            <Col xs={12} md={4} key={tile.key} className='mb-4'>
              <Card size='small'>
                <Statistic
                  title={tile.label}
                  value={summary[tile.key] ?? 0}
                  valueStyle={tile.key === 'total' ? undefined : {color: undefined}}
                  prefix={tile.icon}
                />
              </Card>
            </Col>
          ))}
        </Row>

        {wards.length === 0 && !loading && <Empty description={t('No beds have been configured yet')} />}

        {Object.keys(bedsByWard).map((wardName) => {
          const wardMeta = wards.find((w) => w.ward_name === wardName)
          return (
            <Card
              key={wardName}
              className='mb-4'
              title={
                <span>
                  {wardName}{' '}
                  <span className='text-muted fs-8'>
                    ({wardMeta ? `${wardMeta.occupied}/${wardMeta.total} ${t('occupied')}` : ''})
                  </span>
                </span>
              }
              size='small'
            >
              <Row gutter={[12, 12]}>
                {bedsByWard[wardName].map((bed) => {
                  const meta = STATUS_META[bed.bed_status] || STATUS_META.vacant
                  return (
                    <Col key={bed.id} xs={12} sm={8} md={6} lg={4}>
                      <Card
                        size='small'
                        className='h-100'
                        style={{borderTop: `3px solid var(--bs-${meta.color === 'default' ? 'secondary' : meta.color})`}}
                      >
                        <div className='fw-bolder mb-1'>{bed.bed_number}</div>
                        {bed.bed_type && <div className='text-muted fs-8 mb-1'>{bed.bed_type}</div>}
                        <Tag color={meta.color} className='mb-2'>
                          {meta.icon} {t(meta.label)}
                        </Tag>
                        {bed.bed_status === 'occupied' ? (
                          <div className='fs-8'>
                            <div className='fw-bold'>{bed.patient_name || '-'}</div>
                            <div className='text-muted'>{bed.admission_no}</div>
                          </div>
                        ) : (
                          <Select
                            size='small'
                            style={{width: '100%'}}
                            value={bed.bed_status}
                            loading={updatingBedId === bed.id}
                            onChange={(value) => handleStatusChange(bed.id, value)}
                          >
                            {MANUAL_STATUSES.map((s) => (
                              <Option key={s} value={s}>
                                {t(STATUS_META[s].label)}
                              </Option>
                            ))}
                          </Select>
                        )}
                      </Card>
                    </Col>
                  )
                })}
              </Row>
            </Card>
          )
        })}
      </Spin>
    </div>
  )
}

export default BedDashboardController

import React, {FC, useEffect, useState} from 'react'
import {Card, Col, Row, Tag, Spin, Button, Empty} from 'antd'
import {ReloadOutlined} from '@ant-design/icons'
import {ErVisitApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'
import ErVisitViewController from '../ErVisit/View/ErVisitView.controller'

const triageColor = (color?: string): string => {
  const map: Record<string, string> = {red: 'red', orange: 'orange', yellow: 'gold', green: 'green', blue: 'blue'}
  return color ? map[color] || 'default' : 'default'
}

const statusColor = (status: string): string => {
  switch (status) {
    case 'waiting_triage':
      return 'red'
    case 'triaged':
      return 'gold'
    case 'in_treatment':
      return 'blue'
    default:
      return 'default'
  }
}

const ElapsedBadge: FC<{since: string; targetMinutes?: number}> = ({since, targetMinutes}) => {
  const [elapsedMin, setElapsedMin] = useState(0)
  useEffect(() => {
    const compute = () => setElapsedMin(Math.floor((Date.now() - new Date(since).getTime()) / 60000))
    compute()
    const interval = setInterval(compute, 15000)
    return () => clearInterval(interval)
  }, [since])
  const overdue = targetMinutes !== undefined && elapsedMin > targetMinutes
  return <Tag color={overdue ? 'red' : 'default'}>{elapsedMin}m</Tag>
}

const ErBoardController: FC = () => {
  const {t} = useLang()
  const {handleErrorMessage} = useErrorHandler()
  const [loading, setLoading] = useState(false)
  const [visits, setVisits] = useState<any[]>([])
  const [selectedId, setSelectedId] = useState<number | null>(null)
  const [showView, setShowView] = useState(false)
  const [reloadView, setReloadView] = useState(0)

  const loadData = () => {
    setLoading(true)
    ErVisitApi.board()
      .then((res: any) => setVisits(res?.data?.data ?? res?.data ?? []))
      .catch((err) => handleErrorMessage(err))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    const interval = setInterval(loadData, 30000)
    return () => clearInterval(interval)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const openVisit = (id: number) => {
    setSelectedId(id)
    setShowView(true)
  }

  const handleCallbackFunc = (event: any, action: string) => {
    if (action === 'hideForm' || action === 'hideView') {
      setShowView(false)
    }
    if (action === 'reloadListing' || action === 'reloadView') {
      loadData()
      setReloadView((r) => r + 1)
    }
  }

  const sorted = [...visits].sort((a, b) => {
    const levelA = a.current_triage?.triage_level ?? 6
    const levelB = b.current_triage?.triage_level ?? 6
    return levelA - levelB
  })

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <h3 className='mb-0'>{t('Emergency Board')}</h3>
        <Button icon={<ReloadOutlined />} onClick={loadData} loading={loading}>
          {t('Refresh')}
        </Button>
      </div>

      <Spin spinning={loading}>
        {sorted.length === 0 ? (
          <Empty description={t('No active ER visits')} />
        ) : (
          <Row gutter={[16, 16]}>
            {sorted.map((visit) => (
              <Col key={visit.id} xs={24} sm={12} md={8} lg={6}>
                <Card
                  size='small'
                  hoverable
                  onClick={() => openVisit(visit.id)}
                  style={{borderTop: `4px solid var(--bs-${triageColor(visit.current_triage?.color_band) === 'default' ? 'secondary' : triageColor(visit.current_triage?.color_band)})`}}
                >
                  <div className='d-flex justify-content-between align-items-start mb-2'>
                    <strong>{visit.er_visit_no}</strong>
                    <ElapsedBadge since={visit.arrival_at} targetMinutes={visit.current_triage?.target_minutes} />
                  </div>
                  <div className='fw-bold mb-1'>{visit.patient_name}</div>
                  <div className='text-muted fs-8 mb-2'>{visit.chief_complaint}</div>
                  <div className='d-flex justify-content-between align-items-center'>
                    <Tag color={statusColor(visit.er_status)} className='text-capitalize'>
                      {visit.er_status_label}
                    </Tag>
                    {visit.current_triage ? (
                      <Tag color={triageColor(visit.current_triage.color_band)}>Level {visit.current_triage.triage_level}</Tag>
                    ) : (
                      <Tag>Not Triaged</Tag>
                    )}
                  </div>
                  <div className='text-muted fs-9 mt-1'>{DateTimeUtils.formatDateTimeA(visit.arrival_at)}</div>
                </Card>
              </Col>
            ))}
          </Row>
        )}
      </Spin>

      <ErVisitViewController
        entityId={selectedId}
        reloadView={reloadView}
        isShowView={showView}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default ErBoardController

import {Card, Progress, Tag} from 'antd'
import React, {FC} from 'react'
import {useLang} from 'src/app/hooks/useLang'

interface WardRow {
  ward_id: number
  ward_name: string | null
  total: number
  vacant: number
  occupied: number
  reserved: number
  cleaning: number
  maintenance: number
}

interface BedOccupancyData {
  summary: {total: number; vacant: number; occupied: number; reserved: number; cleaning: number; maintenance: number}
  wards: WardRow[]
}

const BedOccupancyWidget: FC<{data: BedOccupancyData}> = ({data}) => {
  const {t} = useLang()
  const {summary, wards} = data
  const occupancyPercent = summary.total > 0 ? Math.round((summary.occupied / summary.total) * 100) : 0

  return (
    <Card className='h-100' title={t('Bed Occupancy')}>
      <Progress
        percent={occupancyPercent}
        format={(percent) => `${percent}% (${summary.occupied}/${summary.total})`}
        status={occupancyPercent >= 90 ? 'exception' : 'active'}
      />
      <div className='mt-4' style={{maxHeight: 220, overflowY: 'auto'}}>
        {wards.length === 0 && <div className='text-center text-muted py-6'>{t('No data found!')}</div>}
        {wards.map((ward) => (
          <div key={ward.ward_id} className='d-flex align-items-center justify-content-between py-2 border-bottom'>
            <span className='fw-semibold'>{ward.ward_name || t('Unassigned')}</span>
            <span>
              <Tag color='green'>{t('Vacant')}: {ward.vacant}</Tag>
              <Tag color='red'>{t('Occupied')}: {ward.occupied}</Tag>
            </span>
          </div>
        ))}
      </div>
    </Card>
  )
}

export default React.memo(BedOccupancyWidget)

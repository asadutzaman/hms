import {Card, Statistic} from 'antd'
import React, {FC} from 'react'
import {useNavigate} from 'react-router-dom'

interface KpiStatTileProps {
  title: string
  value: number | string
  suffix?: string
  precision?: number
  navigateTo?: string
  valueColor?: string
}

const KpiStatTile: FC<KpiStatTileProps> = ({title, value, suffix, precision, navigateTo, valueColor}) => {
  const navigate = useNavigate()

  return (
    <Card
      hoverable={!!navigateTo}
      onClick={navigateTo ? () => navigate(navigateTo) : undefined}
      className='h-100'
    >
      <Statistic
        title={title}
        value={value}
        suffix={suffix}
        precision={precision}
        valueStyle={valueColor ? {color: valueColor} : undefined}
      />
    </Card>
  )
}

export default React.memo(KpiStatTile)

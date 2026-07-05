import React, {FC, useEffect, useState} from 'react'
import {Alert, Badge, Tag} from 'antd'
import {WarningOutlined} from '@ant-design/icons'
import {ReportInvApi} from 'src/app/api'

// Pull-based "auto reorder" alert — computed on load from the session
// branch's stock vs each item's reorder_qty (no background job/notification
// infra exists in this codebase, so this surfaces wherever it's embedded
// rather than pushing a notification).
const ReorderAlertsWidget: FC = () => {
  const [alerts, setAlerts] = useState<any[]>([])

  useEffect(() => {
    ReportInvApi.getItemLowStockAlerts()
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setAlerts(data.alerts || [])
      })
      .catch(() => setAlerts([]))
  }, [])

  if (!alerts.length) return null

  return (
    <Alert
      type='warning'
      showIcon
      icon={<WarningOutlined />}
      className='mb-4'
      message={
        <span>
          Reorder Alerts <Badge count={alerts.length} style={{backgroundColor: '#faad14'}} />
        </span>
      }
      description={
        <div className='d-flex flex-wrap gap-2 mt-1'>
          {alerts.map((a) => (
            <Tag color='orange' key={a.item_id}>
              {a.item_name}: {a.balance_qty} / {a.reorder_qty}
            </Tag>
          ))}
        </div>
      }
    />
  )
}

export default ReorderAlertsWidget

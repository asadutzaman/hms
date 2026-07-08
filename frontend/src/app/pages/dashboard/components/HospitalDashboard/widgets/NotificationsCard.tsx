import React, {FC, useEffect, useState} from 'react'
import {NotificationApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import KpiStatTile from './KpiStatTile'

const NotificationsCard: FC = () => {
  const {t} = useLang()
  const [unreadCount, setUnreadCount] = useState(0)

  useEffect(() => {
    NotificationApi.unreadCount()
      .then((res: any) => setUnreadCount(res?.data?.count ?? 0))
      .catch(() => setUnreadCount(0))
  }, [])

  return (
    <KpiStatTile
      title={t('Unread Notifications')}
      value={unreadCount}
      valueColor={unreadCount > 0 ? '#f1416c' : undefined}
    />
  )
}

export default React.memo(NotificationsCard)

import {FC, useEffect, useState} from 'react'
import {Empty, Spin} from 'antd'
import {NotificationApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'

const NotificationMenu: FC = () => {
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [notifications, setNotifications] = useState<any[]>([])

  const loadData = () => {
    setLoading(true)
    NotificationApi.my()
      .then((res: any) => setNotifications(res?.data?.data ?? res?.data ?? []))
      .catch(() => setNotifications([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const handleMarkRead = (id: any) => {
    NotificationApi.markRead(id)
      .then(() => loadData())
      .catch(() => {})
  }

  const handleMarkAllRead = () => {
    NotificationApi.markAllRead()
      .then(() => loadData())
      .catch(() => {})
  }

  const unreadCount = notifications.filter((n) => !n.is_read).length

  return (
    <div
      className='menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-350px'
      data-kt-menu='true'
    >
      <div className='d-flex justify-content-between align-items-center px-5 pb-3'>
        <div className='fw-bolder fs-5'>{t('Notifications')}</div>
        {unreadCount > 0 && (
          <a href='#' className='fs-8 text-primary' onClick={(e) => { e.preventDefault(); handleMarkAllRead() }}>
            {t('Mark all read')}
          </a>
        )}
      </div>
      <div className='separator mb-2'></div>

      <div style={{maxHeight: 350, overflowY: 'auto'}}>
        {loading ? (
          <div className='text-center py-5'>
            <Spin size='small' />
          </div>
        ) : notifications.length === 0 ? (
          <div className='px-5 py-3'>
            <Empty description={t('No notifications yet')} image={Empty.PRESENTED_IMAGE_SIMPLE} />
          </div>
        ) : (
          notifications.map((n: any) => (
            <div
              key={n.id}
              className='menu-item px-3 cursor-pointer'
              onClick={() => !n.is_read && handleMarkRead(n.id)}
              style={{backgroundColor: n.is_read ? 'transparent' : 'rgba(0,158,247,0.08)'}}
            >
              <div className='menu-content px-3 py-2'>
                <div className='fw-bolder fs-7'>{n.title}</div>
                <div className='text-muted fs-8'>{n.body}</div>
                <div className='text-muted fs-9 mt-1'>{DateTimeUtils.formatDateTimeA(n.created_at)}</div>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  )
}

export {NotificationMenu}

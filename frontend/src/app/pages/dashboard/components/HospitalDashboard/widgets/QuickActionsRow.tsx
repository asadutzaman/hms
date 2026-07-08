import {Button, Card, Space} from 'antd'
import React, {FC} from 'react'
import {useNavigate} from 'react-router-dom'
import {KTIcon} from 'src/_metronic/helpers'
import {usePermissionContext} from 'src/app/hooks/context/usePermissionContext'
import {useLang} from 'src/app/hooks/useLang'

interface QuickAction {
  label: string
  icon: string
  permission: string
  navigateTo: string
}

const QuickActionsRow: FC = () => {
  const {t} = useLang()
  const navigate = useNavigate()
  const {hasPermission} = usePermissionContext()

  const actions: QuickAction[] = [
    {label: t('New Appointment'), icon: 'calendar-add', permission: 'auth:appointment:create', navigateTo: '/admin/appointment/create'},
    {label: t('New Patient'), icon: 'profile-user', permission: 'auth:patient:create', navigateTo: '/admin/patient/list'},
    {label: t('New OPD Visit'), icon: 'hospital', permission: 'auth:opd:create', navigateTo: '/admin/opd/create'},
  ]

  const visibleActions = actions.filter((action) => hasPermission(action.permission))
  if (visibleActions.length === 0) {
    return null
  }

  return (
    <Card title={t('Quick Actions')}>
      <Space wrap>
        {visibleActions.map((action) => (
          <Button key={action.navigateTo} type='primary' onClick={() => navigate(action.navigateTo)}>
            <KTIcon iconName={action.icon} className='fs-3 me-2' />
            {action.label}
          </Button>
        ))}
      </Space>
    </Card>
  )
}

export default React.memo(QuickActionsRow)

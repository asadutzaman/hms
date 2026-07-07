import React from 'react'
import {Route, Routes} from 'react-router-dom'
import PermissionRoutes from '../permission/PermissionRoutes'
import CompanyRoutes from '../company/CompanyRoutes'
import ProfileRoutes from '../profile/ProfileRoutes'
import NotificationTemplateListController from './components/NotificationTemplate/List/NotificationTemplateList.controller'
import BackupController from './components/Backup/Backup.controller'

const SettingRoutes = () => {
  return (
    <Routes>
      <Route path={'/company/*'} element={<CompanyRoutes />} />
      <Route path={'/profile/*'} element={<ProfileRoutes />} />
      <Route path={'/permission/*'} element={<PermissionRoutes />} />
      <Route path={'/notification-template'} element={<NotificationTemplateListController />} />
      <Route path={'/backup'} element={<BackupController />} />
    </Routes>
  )
}

export default SettingRoutes

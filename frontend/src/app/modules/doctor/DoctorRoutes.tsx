import React from 'react'
import {Navigate, Route, Routes} from 'react-router-dom'

import DoctorDashboardController from './components/Dashboard/DoctorDashboard.controller'
import DailyAppointmentListController from './components/DailyAppointmentList/DailyAppointmentList.controller'
import PrescriptionTemplateListController from './components/PrescriptionTemplate/PrescriptionTemplateList.controller'

const DoctorRoutes = () => {
  return (
    <Routes>
      {/* Default → dashboard */}
      <Route index element={<Navigate to='dashboard' replace />} />

      <Route path='dashboard' element={<DoctorDashboardController />} />
      <Route path='daily-appointments' element={<DailyAppointmentListController />} />
      <Route path='prescription-templates' element={<PrescriptionTemplateListController />} />

      {/* Fallback */}
      <Route path='*' element={<Navigate to='dashboard' replace />} />
    </Routes>
  )
}

export default DoctorRoutes

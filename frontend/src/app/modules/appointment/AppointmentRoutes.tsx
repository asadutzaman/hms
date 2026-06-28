import React from 'react'
import { Navigate, Route, Routes } from 'react-router-dom'

import AppointmentListController from './components/Appointment/List/AppointmentList.controller'
import AppointmentFormController from './components/Appointment/Form/AppointmentForm.controller'
import AppointmentViewController from './components/Appointment/View/AppointmentView.controller'
import WalkInController from './components/WalkIn/WalkIn.controller'
import QueueController from './components/Queue/Queue.controller'

import DoctorScheduleListController from './components/DoctorSchedule/List/DoctorScheduleList.controller'
import DoctorScheduleFormController from './components/DoctorSchedule/Form/DoctorScheduleForm.controller'
import DoctorScheduleViewController from './components/DoctorSchedule/View/DoctorScheduleView.controller'

const AppointmentRoutes = () => {
  return (
    <Routes>
      {/* Default → list */}
      <Route index element={<Navigate to='list' replace />} />

      {/* Appointment CRUD */}
      <Route path='list' element={<AppointmentListController />} />
      <Route path='create' element={<AppointmentFormController />} />
      <Route path='edit/:id' element={<AppointmentFormController />} />
      <Route path='view/:id' element={<AppointmentViewController />} />

      {/* Walk-in & Queue */}
      <Route path='walk-in' element={<WalkInController />} />
      <Route path='queue' element={<QueueController />} />

      {/* Doctor Schedule CRUD */}
      <Route path='schedules' element={<DoctorScheduleListController />} />
      <Route path='schedules/create' element={<DoctorScheduleFormController />} />
      <Route path='schedules/edit/:id' element={<DoctorScheduleFormController />} />
      <Route path='schedules/view/:id' element={<DoctorScheduleViewController />} />

      {/* Fallback */}
      <Route path='*' element={<Navigate to='list' replace />} />
    </Routes>
  )
}

export default AppointmentRoutes
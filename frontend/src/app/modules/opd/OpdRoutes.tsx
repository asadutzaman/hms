import React from 'react'
import { Navigate, Route, Routes } from 'react-router-dom'

import OpdVisitListController from './components/OpdVisit/List/OpdVisitList.controller'
import OpdVisitFormController from './components/OpdVisit/Form/OpdVisitForm.controller'
import OpdVisitViewController from './components/OpdVisit/View/OpdVisitView.controller'
import WalkInController from './components/WalkIn/WalkIn.controller'
import QueueController from './components/Queue/Queue.controller'
import DisplayBoardController from './components/DisplayBoard/DisplayBoard.controller'

import DoctorScheduleListController from './components/DoctorSchedule/List/DoctorScheduleList.controller'
import DoctorScheduleFormController from './components/DoctorSchedule/Form/DoctorScheduleForm.controller'
import DoctorScheduleViewController from './components/DoctorSchedule/View/DoctorScheduleView.controller'

const OpdVisitRoutes = () => {
  return (
    <Routes>
      {/* Default → list */}
      <Route index element={<Navigate to='list' replace />} />

      {/* OpdVisit CRUD */}
      <Route path='list' element={<OpdVisitListController />} />
      <Route path='create' element={<OpdVisitFormController />} />
      <Route path='edit/:id' element={<OpdVisitFormController />} />
      <Route path='view/:id' element={<OpdVisitViewController />} />

      {/* Walk-in & Queue */}
      <Route path='walk-in' element={<WalkInController />} />
      <Route path='queue' element={<QueueController />} />
      <Route path='display-board' element={<DisplayBoardController />} />

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

export default OpdVisitRoutes
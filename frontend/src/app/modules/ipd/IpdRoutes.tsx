import React from 'react'
import {Route, Routes} from 'react-router-dom'
import WardListController from './components/Ward/List/WardList.controller'
import BedListController from './components/Bed/List/BedList.controller'
import IpdAdmissionListController from './components/IpdAdmission/List/IpdAdmissionList.controller'
import BedDashboardController from './components/BedDashboard/BedDashboard.controller'

const IpdRoutes = () => {
  return (
    <Routes>
      <Route path={'/bed-dashboard'} element={<BedDashboardController />} />
      <Route path={'/ward'} element={<WardListController />} />
      <Route path={'/bed'} element={<BedListController />} />
      <Route path={'/admission'} element={<IpdAdmissionListController />} />
    </Routes>
  )
}

export default IpdRoutes

import React from 'react'
import {Route, Routes} from 'react-router'
import HrMastersController from './components/Masters/HrMasters.controller'
import AttendanceController from './components/Attendance/Attendance.controller'
import LeaveRequestController from './components/LeaveRequest/LeaveRequest.controller'
import LeaveBalanceController from './components/LeaveBalance/LeaveBalance.controller'

const HrRoutes = () => {
  return (
    <Routes>
      <Route path={'/masters'} element={<HrMastersController />} />
      <Route path={'/attendance'} element={<AttendanceController />} />
      <Route path={'/leave-request'} element={<LeaveRequestController />} />
      <Route path={'/leave-balance'} element={<LeaveBalanceController />} />
    </Routes>
  )
}

export default HrRoutes

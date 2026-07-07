import React from 'react'
import {Navigate, Route, Routes} from 'react-router-dom'
import ProtectedPatientRoute from './components/Guard/ProtectedPatientRoute'
import PatientPortalLayout from './components/Layout/PatientPortalLayout'
import PatientLoginController from './components/Login/PatientLogin.controller'
import PatientDashboardController from './components/Dashboard/PatientDashboard.controller'
import PatientAppointmentsController from './components/Appointments/PatientAppointments.controller'
import PatientPrescriptionsController from './components/Prescriptions/PatientPrescriptions.controller'
import PatientLabReportsController from './components/LabReports/PatientLabReports.controller'
import PatientBillsController from './components/Bills/PatientBills.controller'
import PatientTimelineController from './components/Timeline/PatientTimeline.controller'
import PatientProfileController from './components/Profile/PatientProfile.controller'

/**
 * A fully separate route tree from /admin/* — the patient portal is not
 * part of the staff admin shell (no sidebar, no MasterLayout, no staff
 * ProtectedAdminRoute) per the Sprint 8 architecture decision (see
 * project_hms_sprint8_scope memory).
 */
const PatientPortalRoutes = () => {
  return (
    <Routes>
      <Route path='login' element={<PatientLoginController />} />
      <Route element={<ProtectedPatientRoute />}>
        <Route element={<PatientPortalLayout />}>
          <Route path='dashboard' element={<PatientDashboardController />} />
          <Route path='appointments' element={<PatientAppointmentsController />} />
          <Route path='prescriptions' element={<PatientPrescriptionsController />} />
          <Route path='lab-reports' element={<PatientLabReportsController />} />
          <Route path='bills' element={<PatientBillsController />} />
          <Route path='timeline' element={<PatientTimelineController />} />
          <Route path='profile' element={<PatientProfileController />} />
        </Route>
      </Route>
      <Route index element={<Navigate to='dashboard' replace />} />
      <Route path='*' element={<Navigate to='dashboard' replace />} />
    </Routes>
  )
}

export default PatientPortalRoutes

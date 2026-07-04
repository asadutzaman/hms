import React from 'react'
import {Route, Routes} from 'react-router-dom'
import PatientListController from './components/Patient/List/PatientList.controller'
import MergePatientController from './components/Patient/Merge/MergePatient.controller'

const PatientRoutes = () => {
  return (
    <Routes>
      <Route path='list' element={<PatientListController />} />
      <Route path='merge' element={<MergePatientController />} />
    </Routes>
  )
}

export default PatientRoutes

import React from 'react'
import {Route, Routes} from 'react-router-dom'
import SoapNoteListController from './components/SoapNote/List/SoapNoteList.controller'
import CodeBlueEventListController from './components/CodeBlueEvent/List/CodeBlueEventList.controller'
import DailyReviewListController from './components/DailyReview/List/DailyReviewList.controller'
import ShiftHandoverListController from './components/ShiftHandover/List/ShiftHandoverList.controller'
import DischargeChecklistListController from './components/DischargeChecklist/List/DischargeChecklistList.controller'
import ClinicalJobListController from './components/ClinicalJob/List/ClinicalJobList.controller'
import BleepListController from './components/Bleep/List/BleepList.controller'
import AtoeAssessmentListController from './components/AtoeAssessment/List/AtoeAssessmentList.controller'
import OrderSetListController from './components/OrderSet/List/OrderSetList.controller'

const ClinicalRoutes = () => {
  return (
    <Routes>
      <Route path='soap-notes' element={<SoapNoteListController />} />
      <Route path='code-blue' element={<CodeBlueEventListController />} />
      <Route path='daily-reviews' element={<DailyReviewListController />} />
      <Route path='handovers' element={<ShiftHandoverListController />} />
      <Route path='discharge-checklists' element={<DischargeChecklistListController />} />
      <Route path='clinical-jobs' element={<ClinicalJobListController />} />
      <Route path='bleeps' element={<BleepListController />} />
      <Route path='atoe-assessments' element={<AtoeAssessmentListController />} />
      <Route path='order-sets' element={<OrderSetListController />} />
    </Routes>
  )
}

export default ClinicalRoutes

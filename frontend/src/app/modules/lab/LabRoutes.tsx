import {Route, Routes} from 'react-router-dom'
import LabTestListController from './components/LabTest/List/LabTestList.controller'
import LabOrderWorklistController from './components/LabOrder/LabOrderWorklist.controller'
import LabQcController from './components/LabQc/LabQc.controller'

const LabRoutes = () => {
  return (
    <Routes>
      <Route path={'/lab-test'} element={<LabTestListController />} />
      <Route path={'/lab-order'} element={<LabOrderWorklistController />} />
      <Route path={'/lab-qc'} element={<LabQcController />} />
    </Routes>
  )
}

export default LabRoutes

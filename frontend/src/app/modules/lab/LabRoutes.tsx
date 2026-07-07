import {Route, Routes} from 'react-router-dom'
import LabTestListController from './components/LabTest/List/LabTestList.controller'
import LabOrderWorklistController from './components/LabOrder/LabOrderWorklist.controller'

const LabRoutes = () => {
  return (
    <Routes>
      <Route path={'/lab-test'} element={<LabTestListController />} />
      <Route path={'/lab-order'} element={<LabOrderWorklistController />} />
    </Routes>
  )
}

export default LabRoutes

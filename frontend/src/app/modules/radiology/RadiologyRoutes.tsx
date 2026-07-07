import {Route, Routes} from 'react-router-dom'
import RadiologyTestListController from './components/RadiologyTest/List/RadiologyTestList.controller'
import RadiologyOrderWorklistController from './components/RadiologyOrder/RadiologyOrderWorklist.controller'
import RadiologyReportTemplateListController from './components/RadiologyReportTemplate/List/RadiologyReportTemplateList.controller'

const RadiologyRoutes = () => {
  return (
    <Routes>
      <Route path={'/radiology-test'} element={<RadiologyTestListController />} />
      <Route path={'/radiology-order'} element={<RadiologyOrderWorklistController />} />
      <Route path={'/radiology-report-template'} element={<RadiologyReportTemplateListController />} />
    </Routes>
  )
}

export default RadiologyRoutes

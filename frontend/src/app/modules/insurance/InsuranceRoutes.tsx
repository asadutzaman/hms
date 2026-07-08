import {Route, Routes} from 'react-router-dom'
import InsuranceCompanyListController from './components/InsuranceCompany/List/InsuranceCompanyList.controller'
import PreAuthorizationWorklistController from './components/PreAuthorization/PreAuthorizationWorklist.controller'
import InsuranceClaimWorklistController from './components/InsuranceClaim/InsuranceClaimWorklist.controller'

const InsuranceRoutes = () => {
  return (
    <Routes>
      <Route path={'/insurance-company'} element={<InsuranceCompanyListController />} />
      <Route path={'/pre-authorization'} element={<PreAuthorizationWorklistController />} />
      <Route path={'/claims'} element={<InsuranceClaimWorklistController />} />
    </Routes>
  )
}

export default InsuranceRoutes

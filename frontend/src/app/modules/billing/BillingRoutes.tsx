import {Route, Routes} from 'react-router-dom'
import BillingPackageListController from './components/BillingPackage/List/BillingPackageList.controller'

const BillingRoutes = () => {
  return (
    <Routes>
      <Route path={'/billing-package'} element={<BillingPackageListController />} />
    </Routes>
  )
}

export default BillingRoutes

import {Route, Routes} from 'react-router-dom'
import BillingPackageListController from './components/BillingPackage/List/BillingPackageList.controller'
import PaymentTransactionController from './components/PaymentTransaction/PaymentTransaction.controller'

const BillingRoutes = () => {
  return (
    <Routes>
      <Route path={'/billing-package'} element={<BillingPackageListController />} />
      <Route path={'/payment-transaction'} element={<PaymentTransactionController />} />
    </Routes>
  )
}

export default BillingRoutes

import React from 'react'
import {Route, Routes} from 'react-router'
import DonorController from './components/Donor/Donor.controller'
import DonationController from './components/Donation/Donation.controller'
import InventoryController from './components/Inventory/Inventory.controller'
import CrossMatchTransfusionController from './components/CrossMatchTransfusion/CrossMatchTransfusion.controller'

const BloodBankRoutes = () => {
  return (
    <Routes>
      <Route path={'/donors'} element={<DonorController />} />
      <Route path={'/donations'} element={<DonationController />} />
      <Route path={'/inventory'} element={<InventoryController />} />
      <Route path={'/cross-match'} element={<CrossMatchTransfusionController />} />
    </Routes>
  )
}

export default BloodBankRoutes

import React from 'react'
import {Route, Routes} from 'react-router'
import TheatreController from './components/Theatre/Theatre.controller'
import OtBookingListController from './components/OtBooking/List/OtBookingList.controller'
import OtBookingViewController from './components/OtBooking/View/OtBookingView.controller'

const OtRoutes = () => {
  return (
    <Routes>
      <Route path={'/theatre'} element={<TheatreController />} />
      <Route path={'/booking'} element={<OtBookingListController />} />
      <Route path={'/booking/:id'} element={<OtBookingViewController />} />
    </Routes>
  )
}

export default OtRoutes

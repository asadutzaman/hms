import React from 'react'
import {Route, Routes} from 'react-router-dom'
import ErVisitListController from './components/ErVisit/List/ErVisitList.controller'
import ErBoardController from './components/ErBoard/ErBoard.controller'

const EmergencyRoutes = () => {
  return (
    <Routes>
      <Route path={'/board'} element={<ErBoardController />} />
      <Route path={'/visit'} element={<ErVisitListController />} />
    </Routes>
  )
}

export default EmergencyRoutes

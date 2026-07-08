import {FC} from 'react'
import {PageTitle} from '../../../_metronic/layout/core'
import HospitalDashboardController from './components/HospitalDashboard/HospitalDashboard.controller'

const DashboardWrapper: FC = () => {
  return (
    <>
      <PageTitle breadcrumbs={[]}></PageTitle>
      <HospitalDashboardController />
    </>
  )
}

export {DashboardWrapper}

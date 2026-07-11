import {FC} from 'react'
import {Spin} from 'antd'
import {PageTitle} from '../../../_metronic/layout/core'
import {usePermissionContext} from 'src/app/hooks/context/usePermissionContext'
import HospitalDashboardController from './components/HospitalDashboard/HospitalDashboard.controller'
import {SCOPE as HOSPITAL_DASHBOARD_SCOPE} from './components/HospitalDashboard/HospitalDashboard.listing'
import DoctorDashboardController from 'src/app/modules/doctor/components/Dashboard/DoctorDashboard.controller'

// Role-aware landing dashboard: users who only hold Doctor Portal access
// (no hospital-dashboard widget scopes) get their own worklist dashboard;
// everyone else keeps the hospital-wide dashboard.
const DashboardWrapper: FC = () => {
  const {isPermissionLoaded, hasPermission} = usePermissionContext()

  if (!isPermissionLoaded) {
    return (
      <div className='d-flex justify-content-center p-10'>
        <Spin />
      </div>
    )
  }

  const isDoctorOnly =
    hasPermission('auth:doctorPortal:menuAccess') &&
    !Object.values(HOSPITAL_DASHBOARD_SCOPE).some((scope) => hasPermission(scope))

  return (
    <>
      <PageTitle breadcrumbs={[]}></PageTitle>
      {isDoctorOnly ? <DoctorDashboardController /> : <HospitalDashboardController />}
    </>
  )
}

export {DashboardWrapper}

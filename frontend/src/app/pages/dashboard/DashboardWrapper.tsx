import {FC} from 'react'
import {Spin} from 'antd'
import {PageTitle} from '../../../_metronic/layout/core'
import {usePermissionContext} from 'src/app/hooks/context/usePermissionContext'
import ErrorBoundary from 'src/app/components/ErrorBoundary/ErrorBoundary'
import HospitalDashboardController from './components/HospitalDashboard/HospitalDashboard.controller'
import {SCOPE as HOSPITAL_DASHBOARD_SCOPE} from './components/HospitalDashboard/HospitalDashboard.listing'
import DoctorDashboardController from 'src/app/modules/doctor/components/Dashboard/DoctorDashboard.controller'

// Role-aware landing dashboard: users who only hold Doctor Portal access
// (no hospital-dashboard widget scopes) get their own worklist dashboard;
// everyone else keeps the hospital-wide dashboard. We wait for permissions
// before choosing so a doctor doesn't briefly trigger the hospital summary
// call they can't access; the ErrorBoundary guarantees a render error can
// never blank the whole panel.
const DashboardWrapper: FC = () => {
  const {isPermissionLoaded, hasPermission} = usePermissionContext()

  const renderDashboard = () => {
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

    return isDoctorOnly ? <DoctorDashboardController /> : <HospitalDashboardController />
  }

  return (
    <>
      <PageTitle breadcrumbs={[]}></PageTitle>
      <ErrorBoundary section='dashboard'>{renderDashboard()}</ErrorBoundary>
    </>
  )
}

export {DashboardWrapper}

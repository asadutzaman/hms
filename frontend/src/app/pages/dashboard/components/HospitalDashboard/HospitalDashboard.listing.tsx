import {Col, Row, Spin} from 'antd'
import React, {FC} from 'react'
import {EngageWidget10} from 'src/_metronic/partials/widgets'
import {usePermissionContext} from 'src/app/hooks/context/usePermissionContext'
import {useLang} from 'src/app/hooks/useLang'
import AppointmentStatusDonut from './widgets/AppointmentStatusDonut'
import BedOccupancyWidget from './widgets/BedOccupancyWidget'
import BloodBankInventoryWidget from './widgets/BloodBankInventoryWidget'
import DepartmentRevenueBarChart from './widgets/DepartmentRevenueBarChart'
import KpiStatTile from './widgets/KpiStatTile'
import LowStockAlertsList from './widgets/LowStockAlertsList'
import NotificationsCard from './widgets/NotificationsCard'
import PendingInsuranceClaimsList from './widgets/PendingInsuranceClaimsList'
import QuickActionsRow from './widgets/QuickActionsRow'

export const SCOPE = {
  appointments: 'auth:hospital-dashboard:appointmentsTodayCard',
  opdVisits: 'auth:hospital-dashboard:opdVisitsTodayCard',
  bedOccupancy: 'auth:hospital-dashboard:bedOccupancyCard',
  revenue: 'auth:hospital-dashboard:revenueTrendChart',
  pendingLabOrders: 'auth:hospital-dashboard:pendingLabOrdersCard',
  radiology: 'auth:hospital-dashboard:radiologyTurnaroundCard',
  lowStock: 'auth:hospital-dashboard:lowStockAlertsCard',
  insuranceClaims: 'auth:hospital-dashboard:insuranceClaimsPendingCard',
  bloodBank: 'auth:hospital-dashboard:bloodBankInventoryCard',
  otBookings: 'auth:hospital-dashboard:otBookingsTodayCard',
  attendance: 'auth:hospital-dashboard:hrAttendanceTodayCard',
  notifications: 'auth:hospital-dashboard:notificationsCard',
  quickActions: 'auth:hospital-dashboard:quickActionsWidget',
}

const HospitalDashboardListing: FC<{loading: boolean; data: any}> = ({loading, data}) => {
  const {t} = useLang()
  const {hasPermission} = usePermissionContext()

  const clinicalKpis = data?.clinical_kpis?.kpis || {}
  const departmentBreakdown = data?.clinical_kpis?.department_breakdown || []
  const appointmentsToday = data?.appointments_today || {total_count: 0, status_counts: []}
  const radiologyToday = data?.radiology_today || {total_count: 0, status_counts: [], avg_turnaround_hours: null}
  const otBookingsToday = data?.ot_bookings_today || {total_count: 0, status_counts: []}
  const attendanceToday = data?.attendance_today || {total_employees: 0, present_count: 0, absent_count: 0}
  const bedOccupancy = data?.bed_occupancy || {summary: {total: 0, vacant: 0, occupied: 0, reserved: 0, cleaning: 0, maintenance: 0}, wards: []}
  const insuranceClaims = data?.insurance_claims || {status_counts: [], pending_claims: []}
  const bloodBankInventory = data?.blood_bank_inventory || []
  const lowStockAlerts = data?.low_stock_alerts || []
  const pendingLabOrders = data?.pending_lab_orders || {pending_count: 0}

  const hasAnyDashboardPermission = Object.values(SCOPE).some((scope) => hasPermission(scope))

  if (!hasAnyDashboardPermission) {
    return (
      <div className='row g-5 g-xl-10'>
        <div className='col-xxl-12'>
          <EngageWidget10 className='h-md-100' />
        </div>
      </div>
    )
  }

  return (
    <div className='p-6'>
      <Spin spinning={loading}>
        {hasPermission(SCOPE.quickActions) && (
          <Row gutter={[16, 16]} className='mb-4'>
            <Col span={24}>
              <QuickActionsRow />
            </Col>
          </Row>
        )}

        <Row gutter={[16, 16]} className='mb-4'>
          {hasPermission(SCOPE.appointments) && (
            <Col xs={24} sm={12} md={8} lg={6}>
              <KpiStatTile
                title={t('Appointments Today')}
                value={appointmentsToday.total_count}
                navigateTo='/admin/appointment/list'
              />
            </Col>
          )}
          {hasPermission(SCOPE.opdVisits) && (
            <Col xs={24} sm={12} md={8} lg={6}>
              <KpiStatTile
                title={t('OPD Visits')}
                value={clinicalKpis.opd_visit_count || 0}
                navigateTo='/admin/opd/list'
              />
            </Col>
          )}
          {hasPermission(SCOPE.revenue) && (
            <Col xs={24} sm={12} md={8} lg={6}>
              <KpiStatTile
                title={t('Total Revenue')}
                value={clinicalKpis.total_revenue || 0}
                precision={2}
              />
            </Col>
          )}
          {hasPermission(SCOPE.pendingLabOrders) && (
            <Col xs={24} sm={12} md={8} lg={6}>
              <KpiStatTile
                title={t('Pending Lab Orders')}
                value={pendingLabOrders.pending_count}
                navigateTo='/admin/lab/lab-order'
              />
            </Col>
          )}
          {hasPermission(SCOPE.radiology) && (
            <Col xs={24} sm={12} md={8} lg={6}>
              <KpiStatTile
                title={t('Radiology Orders Today')}
                value={radiologyToday.total_count}
                suffix={radiologyToday.avg_turnaround_hours != null ? `(${t('Avg')} ${radiologyToday.avg_turnaround_hours}h)` : undefined}
                navigateTo='/admin/radiology/radiology-order'
              />
            </Col>
          )}
          {hasPermission(SCOPE.otBookings) && (
            <Col xs={24} sm={12} md={8} lg={6}>
              <KpiStatTile
                title={t('OT Bookings Today')}
                value={otBookingsToday.total_count}
                navigateTo='/admin/ot/booking'
              />
            </Col>
          )}
          {hasPermission(SCOPE.attendance) && (
            <Col xs={24} sm={12} md={8} lg={6}>
              <KpiStatTile
                title={t('Staff Present Today')}
                value={attendanceToday.present_count}
                suffix={`/ ${attendanceToday.total_employees}`}
                navigateTo='/admin/hr/attendance'
              />
            </Col>
          )}
          {hasPermission(SCOPE.notifications) && (
            <Col xs={24} sm={12} md={8} lg={6}>
              <NotificationsCard />
            </Col>
          )}
        </Row>

        <Row gutter={[16, 16]} className='mb-4'>
          {hasPermission(SCOPE.appointments) && (
            <Col xs={24} lg={12}>
              <AppointmentStatusDonut statusCounts={appointmentsToday.status_counts} />
            </Col>
          )}
          {hasPermission(SCOPE.revenue) && (
            <Col xs={24} lg={12}>
              <DepartmentRevenueBarChart departmentBreakdown={departmentBreakdown} />
            </Col>
          )}
        </Row>

        <Row gutter={[16, 16]} className='mb-4'>
          {hasPermission(SCOPE.bedOccupancy) && (
            <Col xs={24} lg={12}>
              <BedOccupancyWidget data={bedOccupancy} />
            </Col>
          )}
          {hasPermission(SCOPE.bloodBank) && (
            <Col xs={24} lg={12}>
              <BloodBankInventoryWidget data={bloodBankInventory} />
            </Col>
          )}
        </Row>

        <Row gutter={[16, 16]} className='mb-4'>
          {hasPermission(SCOPE.insuranceClaims) && (
            <Col xs={24} lg={12}>
              <PendingInsuranceClaimsList data={insuranceClaims} />
            </Col>
          )}
          {hasPermission(SCOPE.lowStock) && (
            <Col xs={24} lg={12}>
              <LowStockAlertsList data={lowStockAlerts} />
            </Col>
          )}
        </Row>
      </Spin>
    </div>
  )
}

export default React.memo(HospitalDashboardListing)

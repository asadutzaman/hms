import React, {FC, useEffect, useState} from 'react'
import {ReportInvApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import MisDashboardListFilter from './MisDashboardList.filter'
import MisDashboardListing from './MisDashboardList.listing'

const today = new Date().toISOString().slice(0, 10)
const startOfMonth = today.slice(0, 8) + '01'

const initialFilters = {
  start_date: startOfMonth,
  end_date: today,
}

const initialKpis = {
  opd_visit_count: 0,
  opd_revenue: 0,
  ipd_admission_count: 0,
  ipd_revenue: 0,
  total_revenue: 0,
  total_beds: 0,
  occupied_beds: 0,
  occupancy_percent: 0,
}

const MisDashboardListController: FC = () => {
  const [filters, setFilters] = useState<any>(initialFilters)
  const [loading, setLoading] = useState<boolean>(false)
  const [kpis, setKpis] = useState<any>(initialKpis)
  const [departmentBreakdown, setDepartmentBreakdown] = useState<any[]>([])

  const {handleErrorMessage} = useErrorHandler()

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const loadData = () => {
    setLoading(true)
    ReportInvApi.getMisDashboard(filters)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setKpis(data.kpis || initialKpis)
        setDepartmentBreakdown(data.department_breakdown || [])
        setLoading(false)
      })
      .catch((err) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }

  return (
    <div className='card'>
      <MisDashboardListFilter
        filters={filters}
        setFilters={setFilters}
        loading={loading}
        handlePreview={loadData}
      />
      <MisDashboardListing loading={loading} kpis={kpis} departmentBreakdown={departmentBreakdown} />
    </div>
  )
}

export default MisDashboardListController

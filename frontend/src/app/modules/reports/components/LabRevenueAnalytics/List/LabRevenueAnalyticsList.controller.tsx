import React, {FC, useEffect, useState} from 'react'
import {ReportInvApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import LabRevenueAnalyticsListFilter from './LabRevenueAnalyticsList.filter'
import LabRevenueAnalyticsListing from './LabRevenueAnalyticsList.listing'

const today = new Date().toISOString().slice(0, 10)
const startOfMonth = today.slice(0, 8) + '01'

const initialFilters = {start_date: startOfMonth, end_date: today, tat_target_hours: 24}
const initialSummary = {total_orders: 0, total_revenue: 0}
const initialTatSummary = {
  target_hours: 24,
  total_completed: 0,
  within_target_count: 0,
  compliance_rate: 0,
  average_tat_hours: 0,
}

const LabRevenueAnalyticsListController: FC = () => {
  const [filters, setFilters] = useState<any>(initialFilters)
  const [loading, setLoading] = useState<boolean>(false)
  const [testWise, setTestWise] = useState<any[]>([])
  const [summary, setSummary] = useState<any>(initialSummary)
  const [tatSummary, setTatSummary] = useState<any>(initialTatSummary)

  const {handleErrorMessage} = useErrorHandler()

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const loadData = () => {
    setLoading(true)
    ReportInvApi.getLabRevenueAnalyticsReport(filters)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setTestWise(data.test_wise || [])
        setSummary(data.summary || initialSummary)
        setTatSummary(data.tat_summary || initialTatSummary)
        setLoading(false)
      })
      .catch((err) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }

  return (
    <div className='card'>
      <LabRevenueAnalyticsListFilter
        filters={filters}
        setFilters={setFilters}
        loading={loading}
        handlePreview={loadData}
      />
      <LabRevenueAnalyticsListing
        loading={loading}
        testWise={testWise}
        summary={summary}
        tatSummary={tatSummary}
      />
    </div>
  )
}

export default LabRevenueAnalyticsListController

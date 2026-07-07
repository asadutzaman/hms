import React, {FC, useEffect, useState} from 'react'
import {ReportInvApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import OccupancyRevenueListFilter from './OccupancyRevenueList.filter'
import OccupancyRevenueListing from './OccupancyRevenueList.listing'

const today = new Date().toISOString().slice(0, 10)
const startOfMonth = today.slice(0, 8) + '01'

const initialFilters = {start_date: startOfMonth, end_date: today}
const initialSummary = {total_beds: 0, occupied_beds: 0, total_revenue: 0}

const OccupancyRevenueListController: FC = () => {
  const [filters, setFilters] = useState<any>(initialFilters)
  const [loading, setLoading] = useState<boolean>(false)
  const [listData, setListData] = useState<any[]>([])
  const [summary, setSummary] = useState<any>(initialSummary)

  const {handleErrorMessage} = useErrorHandler()

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const loadData = () => {
    setLoading(true)
    ReportInvApi.getOccupancyRevenueReport(filters)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setListData(data.results || [])
        setSummary(data.summary || initialSummary)
        setLoading(false)
      })
      .catch((err) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }

  return (
    <div className='card'>
      <OccupancyRevenueListFilter
        filters={filters}
        setFilters={setFilters}
        loading={loading}
        handlePreview={loadData}
      />
      <OccupancyRevenueListing loading={loading} listData={listData} summary={summary} />
    </div>
  )
}

export default OccupancyRevenueListController

import React, {FC, useEffect, useState} from 'react'
import {ReportInvApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import PharmacySalesAnalyticsListFilter from './PharmacySalesAnalyticsList.filter'
import PharmacySalesAnalyticsListing from './PharmacySalesAnalyticsList.listing'

const today = new Date().toISOString().slice(0, 10)
const startOfMonth = today.slice(0, 8) + '01'

const initialFilters = {start_date: startOfMonth, end_date: today, limit: 20}

const PharmacySalesAnalyticsListController: FC = () => {
  const [filters, setFilters] = useState<any>(initialFilters)
  const [loading, setLoading] = useState<boolean>(false)
  const [topDrugs, setTopDrugs] = useState<any[]>([])
  const [nearExpiry, setNearExpiry] = useState<any[]>([])
  const [slowMoving, setSlowMoving] = useState<any[]>([])

  const {handleErrorMessage} = useErrorHandler()

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const loadData = () => {
    setLoading(true)
    ReportInvApi.getPharmacySalesAnalyticsReport(filters)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setTopDrugs(data.top_drugs || [])
        setNearExpiry(data.near_expiry || [])
        setSlowMoving(data.slow_moving || [])
        setLoading(false)
      })
      .catch((err) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }

  return (
    <div className='card'>
      <PharmacySalesAnalyticsListFilter
        filters={filters}
        setFilters={setFilters}
        loading={loading}
        handlePreview={loadData}
      />
      <PharmacySalesAnalyticsListing
        loading={loading}
        topDrugs={topDrugs}
        nearExpiry={nearExpiry}
        slowMoving={slowMoving}
      />
    </div>
  )
}

export default PharmacySalesAnalyticsListController

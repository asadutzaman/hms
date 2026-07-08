import React, {FC, useEffect, useState} from 'react'
import {HospitalDashboardApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import HospitalDashboardFilter, {Filters} from './HospitalDashboard.filter'
import HospitalDashboardListing from './HospitalDashboard.listing'

const today = new Date().toISOString().slice(0, 10)

const initialFilters: Filters = {
  start_date: today,
  end_date: today,
  preset: 'today',
}

const HospitalDashboardController: FC = () => {
  const [filters, setFilters] = useState<Filters>(initialFilters)
  const [loading, setLoading] = useState(false)
  const [data, setData] = useState<any>(null)
  const {handleErrorMessage} = useErrorHandler()

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const loadData = () => {
    setLoading(true)
    HospitalDashboardApi.getSummary({start_date: filters.start_date, end_date: filters.end_date})
      .then((res: any) => {
        const payload = res?.data?.data ?? res?.data ?? {}
        setData(payload)
        setLoading(false)
      })
      .catch((err) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }

  return (
    <div className='card'>
      <HospitalDashboardFilter
        filters={filters}
        setFilters={setFilters}
        loading={loading}
        handlePreview={loadData}
      />
      <HospitalDashboardListing loading={loading} data={data} />
    </div>
  )
}

export default HospitalDashboardController

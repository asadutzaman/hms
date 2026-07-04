import React, {FC, useEffect, useState} from 'react'
import {ReportInvApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {Message} from 'src/app/utils'
import download from 'downloadjs'
import DailyCollectionListFilter from './DailyCollectionList.filter'
import DailyCollectionListing from './DailyCollectionList.listing'

const today = new Date().toISOString().slice(0, 10)

const initialFilters = {
  start_date: today,
  end_date: today,
  department_id: null,
  doctor_id: null,
}

const initialSummary = {total_amount: 0, total_transactions: 0, by_payment_method: {}}

const DailyCollectionListController: FC = () => {
  const [filters, setFilters] = useState<any>(initialFilters)
  const [loading, setLoading] = useState<boolean>(false)
  const [exportLoading, setExportLoading] = useState<boolean>(false)
  const [listData, setListData] = useState<any[]>([])
  const [summary, setSummary] = useState<any>(initialSummary)

  const {handleErrorMessage} = useErrorHandler()

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const loadData = () => {
    setLoading(true)
    ReportInvApi.getDailyCollectionReport(filters)
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

  const handleExport = () => {
    setExportLoading(true)
    ReportInvApi.getDailyCollectionReportExport(filters)
      .then((res: any) => {
        if (res.status === 200) {
          download(new Blob([res.data]), 'DailyCollectionReport.xlsx', {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          })
        }
        Message.success('Download Successfully')
        setExportLoading(false)
      })
      .catch(() => {
        Message.error('A network error occurred. Please try again later.')
        setExportLoading(false)
      })
  }

  return (
    <div className='card'>
      <DailyCollectionListFilter
        filters={filters}
        setFilters={setFilters}
        loading={loading}
        exportLoading={exportLoading}
        handlePreview={loadData}
        handleExport={handleExport}
      />
      <DailyCollectionListing loading={loading} listData={listData} summary={summary} />
    </div>
  )
}

export default DailyCollectionListController

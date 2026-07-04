import React, {FC, useEffect, useMemo, useState} from 'react'
import {ReportInvApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {Message} from 'src/app/utils'
import download from 'downloadjs'
import DrugExpiryListFilter from './DrugExpiryList.filter'
import DrugExpiryListing from './DrugExpiryList.listing'

const MAX_DAYS = 90
const initialSummary = {'30': 0, '60': 0, '90': 0}

const DrugExpiryListController: FC = () => {
  const [bucket, setBucket] = useState<string>('30')
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
    ReportInvApi.getDrugExpiryReport({days: MAX_DAYS})
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

  const filteredListData = useMemo(
    () => listData.filter((row) => String(row.bucket) === String(bucket)),
    [listData, bucket]
  )

  const handleExport = () => {
    setExportLoading(true)
    ReportInvApi.getDrugExpiryReportExport({days: MAX_DAYS})
      .then((res: any) => {
        if (res.status === 200) {
          download(new Blob([res.data]), 'DrugExpiryReport.xlsx', {
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
      <DrugExpiryListFilter
        bucket={bucket}
        setBucket={setBucket}
        summary={summary}
        loading={loading}
        exportLoading={exportLoading}
        handleExport={handleExport}
        handleRefresh={loadData}
      />
      <DrugExpiryListing loading={loading} listData={filteredListData} />
    </div>
  )
}

export default DrugExpiryListController

import React, {FC, useEffect, useState} from 'react'
import {Card, Spin, DatePicker, Space, Button} from 'antd'
import {ReportInvApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import ReportHeader from 'src/app/components/Header/ReportHeader'
import {useLang} from 'src/app/hooks/useLang'

const {RangePicker} = DatePicker

const ControlledDrugRegisterListController: FC = () => {
  const [loading, setLoading] = useState<boolean>(false)
  const [listData, setListData] = useState<any[]>([])
  const [dateRange, setDateRange] = useState<any>(null)
  const {handleErrorMessage} = useErrorHandler()
  const {t} = useLang()

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const loadData = () => {
    setLoading(true)
    const params: any = {}
    if (dateRange && dateRange.length === 2) {
      params.date_from = dateRange[0].format('YYYY-MM-DD')
      params.date_to = dateRange[1].format('YYYY-MM-DD')
    }
    ReportInvApi.getControlledDrugRegisterReport(params)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setListData(data.results || [])
        setLoading(false)
      })
      .catch((err) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }

  return (
    <div className='card'>
      <div className='p-6'>
        <Space>
          <RangePicker value={dateRange} onChange={setDateRange} />
          <Button type='primary' onClick={loadData}>
            {t('Filter')}
          </Button>
        </Space>
      </div>
      <div className='p-6'>
        <Card>
          <Spin spinning={loading}>
            <div className='listing-page-content'>
              <ReportHeader title={t('Controlled Drug Register')} />
              <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
                <thead>
                  <tr>
                    <th>{t('Serial No.')}</th>
                    <th>{t('Drug')}</th>
                    <th>{t('Patient')}</th>
                    <th>{t('MRN')}</th>
                    <th>{t('Quantity')}</th>
                    <th>{t('Dispensed By')}</th>
                    <th>{t('Witnessed By')}</th>
                    <th>{t('Dispensed At')}</th>
                  </tr>
                </thead>
                <tbody>
                  {listData.length === 0 && (
                    <tr>
                      <td colSpan={8} align='center'>
                        {t('No data found!')}
                      </td>
                    </tr>
                  )}
                  {listData.map((row: any, index: number) => (
                    <tr key={row.id}>
                      <td align='center'>{index + 1}</td>
                      <td>{row.drug_name}</td>
                      <td>{row.patient_name}</td>
                      <td>{row.patient_no}</td>
                      <td>{row.dispensed_quantity}</td>
                      <td>{row.dispensed_by_name}</td>
                      <td>{row.witnessed_by_name}</td>
                      <td>{row.dispensed_at}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </Spin>
        </Card>
      </div>
    </div>
  )
}

export default ControlledDrugRegisterListController

import {Card, Spin, Tag} from 'antd'
import React, {FC} from 'react'
import ReportHeader from 'src/app/components/Header/ReportHeader'
import {useLang} from 'src/app/hooks/useLang'

const PharmacySalesAnalyticsListing: FC<any> = (props) => {
  const {loading, topDrugs, nearExpiry, slowMoving} = props
  const {t} = useLang()

  return (
    <div className='p-6'>
      <Spin spinning={loading}>
        <Card className='mb-4'>
          <div className='listing-page-content'>
            <ReportHeader title={t('Top Drugs by Dispensed Volume')} />
            <p className='text-muted fs-7'>
              {t('estimated_sales_value is an estimate: quantity x average purchase unit price, not exact billed revenue.')}
            </p>
            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>{t('Serial No.')}</th>
                  <th>{t('Drug')}</th>
                  <th>{t('Total Quantity')}</th>
                  <th>{t('Dispense Count')}</th>
                  <th>{t('Avg Unit Price')}</th>
                  <th>{t('Estimated Sales Value')}</th>
                </tr>
              </thead>
              <tbody>
                {topDrugs.length === 0 && (
                  <tr>
                    <td colSpan={6} align='center'>
                      {t('No data found!')}
                    </td>
                  </tr>
                )}
                {topDrugs.map((row: any, index: number) => (
                  <tr key={index}>
                    <td align='center'>{index + 1}</td>
                    <td>{row.generic_name} {row.brand_name ? `(${row.brand_name})` : ''}</td>
                    <td>{row.total_quantity}</td>
                    <td>{row.dispense_count}</td>
                    <td>{row.avg_unit_price}</td>
                    <td>{row.estimated_sales_value}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>

        <Card className='mb-4'>
          <div className='listing-page-content'>
            <ReportHeader title={t('Near-Expiry Stock (Next 90 Days)')} />
            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>{t('Serial No.')}</th>
                  <th>{t('Drug')}</th>
                  <th>{t('Balance Quantity')}</th>
                  <th>{t('Expire Date')}</th>
                </tr>
              </thead>
              <tbody>
                {nearExpiry.length === 0 && (
                  <tr>
                    <td colSpan={4} align='center'>
                      {t('No data found!')}
                    </td>
                  </tr>
                )}
                {nearExpiry.map((row: any, index: number) => (
                  <tr key={index}>
                    <td align='center'>{index + 1}</td>
                    <td>{row.generic_name} {row.brand_name ? `(${row.brand_name})` : ''}</td>
                    <td>{row.balance_quantity}</td>
                    <td>
                      <Tag color='warning'>{row.expire_date}</Tag>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>

        <Card>
          <div className='listing-page-content'>
            <ReportHeader title={t('Slow-Moving Drugs')} />
            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>{t('Serial No.')}</th>
                  <th>{t('Drug')}</th>
                  <th>{t('Balance Quantity')}</th>
                </tr>
              </thead>
              <tbody>
                {slowMoving.length === 0 && (
                  <tr>
                    <td colSpan={3} align='center'>
                      {t('No data found!')}
                    </td>
                  </tr>
                )}
                {slowMoving.map((row: any, index: number) => (
                  <tr key={index}>
                    <td align='center'>{index + 1}</td>
                    <td>{row.generic_name} {row.brand_name ? `(${row.brand_name})` : ''}</td>
                    <td>{row.balance_quantity}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Card>
      </Spin>
    </div>
  )
}

export default React.memo(PharmacySalesAnalyticsListing)

import {Card, Col, Row, Spin, Statistic} from 'antd'
import React, {FC} from 'react'
import ReportHeader from 'src/app/components/Header/ReportHeader'
import {useLang} from 'src/app/hooks/useLang'

const DailyCollectionListing: FC<any> = (props) => {
  const {loading, listData, summary} = props
  const {t} = useLang()

  const byPaymentMethod = summary?.by_payment_method || {}

  return (
    <div className='p-6'>
      <Spin spinning={loading}>
        <Row gutter={[16, 16]} className='mb-4'>
          <Col span={6}>
            <Card>
              <Statistic title={t('Total Collection')} value={summary?.total_amount || 0} precision={2} />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic title={t('Total Transactions')} value={summary?.total_transactions || 0} />
            </Card>
          </Col>
          {Object.keys(byPaymentMethod).map((method) => (
            <Col span={4} key={method}>
              <Card>
                <Statistic
                  title={t(method.charAt(0).toUpperCase() + method.slice(1))}
                  value={byPaymentMethod[method]}
                  precision={2}
                />
              </Card>
            </Col>
          ))}
        </Row>

        <Card>
          <div className='listing-page-content'>
            <ReportHeader title={t('Daily Collection Report')} />

            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>{t('Serial No.')}</th>
                  <th>{t('Date')}</th>
                  <th>{t('Department')}</th>
                  <th>{t('Doctor')}</th>
                  <th>{t('Payment Method')}</th>
                  <th>{t('Transactions')}</th>
                  <th>{t('Total Amount')}</th>
                </tr>
              </thead>
              <tbody>
                {listData.length === 0 && (
                  <tr>
                    <td colSpan={7} align='center'>
                      {t('No data found!')}
                    </td>
                  </tr>
                )}
                {listData.map((row: any, index: number) => (
                  <tr key={index}>
                    <td align='center'>{index + 1}</td>
                    <td>{row.collection_date}</td>
                    <td>{row.department_name || '-'}</td>
                    <td>{row.doctor_name || '-'}</td>
                    <td className='text-capitalize'>{row.payment_method}</td>
                    <td>{row.transaction_count}</td>
                    <td>{row.total_amount}</td>
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

export default React.memo(DailyCollectionListing)

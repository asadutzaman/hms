import {Card, Col, Row, Spin, Statistic} from 'antd'
import React, {FC} from 'react'
import ReportHeader from 'src/app/components/Header/ReportHeader'
import {useLang} from 'src/app/hooks/useLang'

const LabRevenueAnalyticsListing: FC<any> = (props) => {
  const {loading, testWise, summary, tatSummary} = props
  const {t} = useLang()

  return (
    <div className='p-6'>
      <Spin spinning={loading}>
        <Row gutter={[16, 16]} className='mb-4'>
          <Col span={6}>
            <Card>
              <Statistic title={t('Total Orders')} value={summary?.total_orders || 0} />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic title={t('Total Revenue')} value={summary?.total_revenue || 0} precision={2} />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic
                title={t('TAT Compliance')}
                value={tatSummary?.compliance_rate || 0}
                suffix={`% (target ${tatSummary?.target_hours || 24}h)`}
              />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic title={t('Avg TAT (hours)')} value={tatSummary?.average_tat_hours || 0} />
            </Card>
          </Col>
        </Row>

        <Card>
          <div className='listing-page-content'>
            <ReportHeader title={t('Test-wise Revenue')} />
            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>{t('Serial No.')}</th>
                  <th>{t('Test')}</th>
                  <th>{t('Order Count')}</th>
                  <th>{t('Revenue')}</th>
                </tr>
              </thead>
              <tbody>
                {testWise.length === 0 && (
                  <tr>
                    <td colSpan={4} align='center'>
                      {t('No data found!')}
                    </td>
                  </tr>
                )}
                {testWise.map((row: any, index: number) => (
                  <tr key={index}>
                    <td align='center'>{index + 1}</td>
                    <td>{row.test_name || '-'}</td>
                    <td>{row.order_count}</td>
                    <td>{row.revenue}</td>
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

export default React.memo(LabRevenueAnalyticsListing)

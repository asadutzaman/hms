import {Card, Col, Row, Spin, Statistic} from 'antd'
import React, {FC} from 'react'
import ReportHeader from 'src/app/components/Header/ReportHeader'
import {useLang} from 'src/app/hooks/useLang'

const OccupancyRevenueListing: FC<any> = (props) => {
  const {loading, listData, summary} = props
  const {t} = useLang()

  const overallOccupancy =
    summary?.total_beds > 0 ? Math.round((summary.occupied_beds / summary.total_beds) * 1000) / 10 : 0

  return (
    <div className='p-6'>
      <Spin spinning={loading}>
        <Row gutter={[16, 16]} className='mb-4'>
          <Col span={6}>
            <Card>
              <Statistic title={t('Total Beds')} value={summary?.total_beds || 0} />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic title={t('Occupied Beds')} value={summary?.occupied_beds || 0} />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic title={t('Occupancy Rate')} value={overallOccupancy} suffix='%' />
            </Card>
          </Col>
          <Col span={6}>
            <Card>
              <Statistic title={t('Room Charge Revenue')} value={summary?.total_revenue || 0} precision={2} />
            </Card>
          </Col>
        </Row>

        <Card>
          <div className='listing-page-content'>
            <ReportHeader title={t('Ward-wise Occupancy & Revenue')} />
            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>{t('Serial No.')}</th>
                  <th>{t('Ward')}</th>
                  <th>{t('Total Beds')}</th>
                  <th>{t('Occupied Beds')}</th>
                  <th>{t('Occupancy %')}</th>
                  <th>{t('Room Charge Revenue')}</th>
                </tr>
              </thead>
              <tbody>
                {listData.length === 0 && (
                  <tr>
                    <td colSpan={6} align='center'>
                      {t('No data found!')}
                    </td>
                  </tr>
                )}
                {listData.map((row: any, index: number) => (
                  <tr key={index}>
                    <td align='center'>{index + 1}</td>
                    <td>{row.ward_name || '-'}</td>
                    <td>{row.total_beds}</td>
                    <td>{row.occupied_beds}</td>
                    <td>{row.occupancy_percent}%</td>
                    <td>{row.room_charge_revenue}</td>
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

export default React.memo(OccupancyRevenueListing)

import {Card, Col, Row, Spin, Statistic} from 'antd'
import React, {FC} from 'react'
import ReportHeader from 'src/app/components/Header/ReportHeader'
import {useLang} from 'src/app/hooks/useLang'

const DoctorProductivityListing: FC<any> = (props) => {
  const {loading, listData, summary} = props
  const {t} = useLang()

  return (
    <div className='p-6'>
      <Spin spinning={loading}>
        <Row gutter={[16, 16]} className='mb-4'>
          <Col span={8}>
            <Card>
              <Statistic title={t('Total OPD Visits')} value={summary?.total_opd_visits || 0} />
            </Card>
          </Col>
          <Col span={8}>
            <Card>
              <Statistic title={t('Total IPD Admissions')} value={summary?.total_ipd_admissions || 0} />
            </Card>
          </Col>
          <Col span={8}>
            <Card>
              <Statistic title={t('Total Revenue')} value={summary?.total_revenue || 0} precision={2} />
            </Card>
          </Col>
        </Row>

        <Card>
          <div className='listing-page-content'>
            <ReportHeader title={t('Doctor-wise Productivity & Revenue')} />
            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>{t('Serial No.')}</th>
                  <th>{t('Doctor')}</th>
                  <th>{t('OPD Visits')}</th>
                  <th>{t('OPD Revenue')}</th>
                  <th>{t('IPD Admissions')}</th>
                  <th>{t('IPD Revenue')}</th>
                  <th>{t('Total Revenue')}</th>
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
                    <td>{row.doctor_name || '-'}</td>
                    <td>{row.opd_visit_count}</td>
                    <td>{row.opd_revenue}</td>
                    <td>{row.ipd_admission_count}</td>
                    <td>{row.ipd_revenue}</td>
                    <td>{row.total_revenue}</td>
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

export default React.memo(DoctorProductivityListing)

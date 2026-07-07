import {Card, Col, Row, Spin, Statistic} from 'antd'
import React, {FC} from 'react'
import Chart from 'react-apexcharts'
import ReportHeader from 'src/app/components/Header/ReportHeader'
import {useLang} from 'src/app/hooks/useLang'

const MisDashboardListing: FC<any> = (props) => {
  const {loading, kpis, departmentBreakdown} = props
  const {t} = useLang()

  const chartCategories = departmentBreakdown.map((row: any) => row.department_name || t('Unassigned'))
  const chartSeries = [
    {
      name: t('Revenue'),
      data: departmentBreakdown.map((row: any) => Number(row.revenue) || 0),
    },
  ]
  const chartOptions: any = {
    chart: {toolbar: {show: false}},
    plotOptions: {bar: {borderRadius: 4, horizontal: false, columnWidth: '45%'}},
    dataLabels: {enabled: false},
    xaxis: {categories: chartCategories},
    colors: ['#009ef7'],
  }

  return (
    <div className='p-6'>
      <Spin spinning={loading}>
        <Row gutter={[16, 16]} className='mb-4'>
          <Col span={8}>
            <Card>
              <Statistic title={t('OPD Visits')} value={kpis?.opd_visit_count || 0} />
            </Card>
          </Col>
          <Col span={8}>
            <Card>
              <Statistic title={t('OPD Revenue')} value={kpis?.opd_revenue || 0} precision={2} />
            </Card>
          </Col>
          <Col span={8}>
            <Card>
              <Statistic title={t('IPD Admissions')} value={kpis?.ipd_admission_count || 0} />
            </Card>
          </Col>
          <Col span={8}>
            <Card>
              <Statistic title={t('IPD Revenue')} value={kpis?.ipd_revenue || 0} precision={2} />
            </Card>
          </Col>
          <Col span={8}>
            <Card>
              <Statistic title={t('Total Revenue')} value={kpis?.total_revenue || 0} precision={2} />
            </Card>
          </Col>
          <Col span={8}>
            <Card>
              <Statistic
                title={t('Bed Occupancy')}
                value={kpis?.occupancy_percent || 0}
                suffix={`% (${kpis?.occupied_beds || 0}/${kpis?.total_beds || 0})`}
              />
            </Card>
          </Col>
        </Row>

        <Card className='mb-4'>
          <div className='listing-page-content'>
            <ReportHeader title={t('Department-wise OPD Revenue')} />
            {chartCategories.length > 0 ? (
              <Chart options={chartOptions} series={chartSeries} type='bar' height={300} />
            ) : (
              <div className='text-center text-muted py-10'>{t('No data found!')}</div>
            )}
          </div>
        </Card>

        <Card>
          <div className='listing-page-content'>
            <ReportHeader title={t('Department Breakdown')} />
            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>{t('Serial No.')}</th>
                  <th>{t('Department')}</th>
                  <th>{t('Visits')}</th>
                  <th>{t('Revenue')}</th>
                </tr>
              </thead>
              <tbody>
                {departmentBreakdown.length === 0 && (
                  <tr>
                    <td colSpan={4} align='center'>
                      {t('No data found!')}
                    </td>
                  </tr>
                )}
                {departmentBreakdown.map((row: any, index: number) => (
                  <tr key={index}>
                    <td align='center'>{index + 1}</td>
                    <td>{row.department_name || t('Unassigned')}</td>
                    <td>{row.visit_count}</td>
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

export default React.memo(MisDashboardListing)

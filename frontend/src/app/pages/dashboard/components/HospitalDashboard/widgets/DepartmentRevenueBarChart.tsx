import {Card} from 'antd'
import React, {FC} from 'react'
import Chart from 'react-apexcharts'
import {useLang} from 'src/app/hooks/useLang'

interface DepartmentBreakdownRow {
  department_name: string | null
  visit_count: number
  revenue: number | string
}

const DepartmentRevenueBarChart: FC<{departmentBreakdown: DepartmentBreakdownRow[]}> = ({
  departmentBreakdown,
}) => {
  const {t} = useLang()
  const chartCategories = departmentBreakdown.map((row) => row.department_name || t('Unassigned'))
  const chartSeries = [
    {
      name: t('Revenue'),
      data: departmentBreakdown.map((row) => Number(row.revenue) || 0),
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
    <Card className='h-100' title={t('Department-wise OPD Revenue')}>
      {chartCategories.length > 0 ? (
        <Chart options={chartOptions} series={chartSeries} type='bar' height={280} />
      ) : (
        <div className='text-center text-muted py-10'>{t('No data found!')}</div>
      )}
    </Card>
  )
}

export default React.memo(DepartmentRevenueBarChart)

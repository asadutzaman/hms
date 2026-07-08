import {Card} from 'antd'
import React, {FC} from 'react'
import Chart from 'react-apexcharts'
import {useLang} from 'src/app/hooks/useLang'

interface StatusCount {
  status: string
  appointment_count: number
}

const STATUS_COLORS: Record<string, string> = {
  confirmed: '#009ef7',
  pending: '#ffc700',
  checked_in: '#7239ea',
  in_consultation: '#50cd89',
  completed: '#50cd89',
  cancelled: '#f1416c',
  no_show: '#f1416c',
  rescheduled: '#ff8a00',
  waitlisted: '#a1a5b7',
  expired: '#a1a5b7',
}

const AppointmentStatusDonut: FC<{statusCounts: StatusCount[]}> = ({statusCounts}) => {
  const {t} = useLang()
  const labels = statusCounts.map((row) => t(row.status))
  const series = statusCounts.map((row) => row.appointment_count)
  const colors = statusCounts.map((row) => STATUS_COLORS[row.status] || '#a1a5b7')

  const chartOptions: any = {
    chart: {toolbar: {show: false}},
    labels,
    colors,
    legend: {position: 'bottom'},
    dataLabels: {enabled: true},
  }

  return (
    <Card className='h-100' title={t('Appointments Today by Status')}>
      {series.length > 0 ? (
        <Chart options={chartOptions} series={series} type='donut' height={280} />
      ) : (
        <div className='text-center text-muted py-10'>{t('No data found!')}</div>
      )}
    </Card>
  )
}

export default React.memo(AppointmentStatusDonut)

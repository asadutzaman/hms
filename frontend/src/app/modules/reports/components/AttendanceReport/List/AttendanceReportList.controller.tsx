import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Col, DatePicker, Row, Spin, Statistic, Typography} from 'antd'
import dayjs from 'dayjs'
import {ReportInvApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'

const {Title} = Typography
const {RangePicker} = DatePicker

const today = new Date().toISOString().slice(0, 10)
const startOfMonth = today.slice(0, 8) + '01'

const initialSummary = {total_employees: 0, total_present_days: 0, total_work_hours: 0}

const AttendanceReportListController: FC = () => {
  const [filters, setFilters] = useState<any>({start_date: startOfMonth, end_date: today})
  const [loading, setLoading] = useState<boolean>(false)
  const [rows, setRows] = useState<any[]>([])
  const [summary, setSummary] = useState<any>(initialSummary)

  const {handleErrorMessage} = useErrorHandler()

  const loadData = () => {
    setLoading(true)
    ReportInvApi.getAttendanceReport(filters)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setRows(data.results || [])
        setSummary(data.summary || initialSummary)
        setLoading(false)
      })
      .catch((err: any) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const handleDateRangeChange = (dates: any) => {
    setFilters({
      ...filters,
      start_date: dates && dates[0] ? dates[0].format('YYYY-MM-DD') : null,
      end_date: dates && dates[1] ? dates[1].format('YYYY-MM-DD') : null,
    })
  }

  return (
    <div className='card'>
      <div className='p-6'>
        <Row gutter={[16, 16]} align='bottom' className='mb-4'>
          <Col span={8}>
            <label className='form-label'>Date Range</label>
            <RangePicker
              className='w-100'
              value={[filters.start_date ? dayjs(filters.start_date) : null, filters.end_date ? dayjs(filters.end_date) : null]}
              onChange={handleDateRangeChange}
            />
          </Col>
          <Col span={16} className='d-flex justify-content-end'>
            <Button type='primary' onClick={loadData} disabled={loading}>
              Preview
            </Button>
          </Col>
        </Row>

        <Spin spinning={loading}>
          <Row gutter={[16, 16]} className='mb-4'>
            <Col span={8}>
              <Card>
                <Statistic title='Employees' value={summary?.total_employees || 0} />
              </Card>
            </Col>
            <Col span={8}>
              <Card>
                <Statistic title='Total Present Days' value={summary?.total_present_days || 0} />
              </Card>
            </Col>
            <Col span={8}>
              <Card>
                <Statistic title='Total Work Hours' value={summary?.total_work_hours || 0} />
              </Card>
            </Col>
          </Row>

          <Card>
            <Title level={5}>Shift-wise Attendance</Title>
            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>Serial No.</th>
                  <th>Employee</th>
                  <th>Shift</th>
                  <th>Present Days</th>
                  <th>Total Work Hours</th>
                  <th>Missing Check-in</th>
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 && (
                  <tr>
                    <td colSpan={6} align='center'>
                      No data found!
                    </td>
                  </tr>
                )}
                {rows.map((row: any, index: number) => (
                  <tr key={index}>
                    <td align='center'>{index + 1}</td>
                    <td>{row.employee_name}</td>
                    <td>{row.shift_name || '-'}</td>
                    <td>{row.present_days}</td>
                    <td>{row.total_work_hours}</td>
                    <td>{row.missing_check_in_count}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </Card>
        </Spin>
      </div>
    </div>
  )
}

export default AttendanceReportListController

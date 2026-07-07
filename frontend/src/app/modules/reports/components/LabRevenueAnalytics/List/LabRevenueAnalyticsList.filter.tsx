import React, {FC} from 'react'
import {Button, Col, DatePicker, InputNumber, Row} from 'antd'
import dayjs from 'dayjs'
import {useLang} from 'src/app/hooks/useLang'

const {RangePicker} = DatePicker

const LabRevenueAnalyticsListFilter: FC<any> = (props) => {
  const {filters, setFilters, loading, handlePreview} = props
  const {t} = useLang()

  const handleDateRangeChange = (dates: any) => {
    setFilters({
      ...filters,
      start_date: dates && dates[0] ? dates[0].format('YYYY-MM-DD') : null,
      end_date: dates && dates[1] ? dates[1].format('YYYY-MM-DD') : null,
    })
  }

  return (
    <div className='p-6'>
      <Row gutter={[16, 16]} align='bottom'>
        <Col span={8}>
          <label className='form-label'>{t('Date Range')}</label>
          <RangePicker
            className='w-100'
            value={[
              filters.start_date ? dayjs(filters.start_date) : null,
              filters.end_date ? dayjs(filters.end_date) : null,
            ]}
            onChange={handleDateRangeChange}
          />
        </Col>
        <Col span={6}>
          <label className='form-label'>{t('TAT Target (hours)')}</label>
          <InputNumber
            className='w-100'
            min={1}
            value={filters.tat_target_hours}
            onChange={(value) => setFilters({...filters, tat_target_hours: value})}
          />
        </Col>
        <Col span={10} className='d-flex justify-content-end'>
          <Button type='primary' onClick={handlePreview} disabled={loading}>
            {t('Preview')}
          </Button>
        </Col>
      </Row>
    </div>
  )
}

export default React.memo(LabRevenueAnalyticsListFilter)

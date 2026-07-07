import React, {FC} from 'react'
import {Button, Col, DatePicker, Row} from 'antd'
import dayjs from 'dayjs'
import {useLang} from 'src/app/hooks/useLang'

const {RangePicker} = DatePicker

const PharmacySalesAnalyticsListFilter: FC<any> = (props) => {
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
        <Col span={16} className='d-flex justify-content-end'>
          <Button type='primary' onClick={handlePreview} disabled={loading}>
            {t('Preview')}
          </Button>
        </Col>
      </Row>
    </div>
  )
}

export default React.memo(PharmacySalesAnalyticsListFilter)

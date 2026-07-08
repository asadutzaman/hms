import {Button, Col, DatePicker, Radio, Row} from 'antd'
import dayjs from 'dayjs'
import React, {FC} from 'react'
import {useLang} from 'src/app/hooks/useLang'

const {RangePicker} = DatePicker

export type PresetKey = 'today' | 'week' | 'month' | 'custom'

export interface Filters {
  start_date: string
  end_date: string
  preset: PresetKey
}

interface HospitalDashboardFilterProps {
  filters: Filters
  setFilters: (filters: Filters) => void
  loading: boolean
  handlePreview: () => void
}

const HospitalDashboardFilter: FC<HospitalDashboardFilterProps> = ({
  filters,
  setFilters,
  loading,
  handlePreview,
}) => {
  const {t} = useLang()

  const applyPreset = (preset: PresetKey) => {
    const today = dayjs()
    let start = today
    let end = today

    if (preset === 'week') {
      start = today.startOf('week')
    } else if (preset === 'month') {
      start = today.startOf('month')
    }

    setFilters({
      ...filters,
      preset,
      start_date: start.format('YYYY-MM-DD'),
      end_date: end.format('YYYY-MM-DD'),
    })
  }

  const handleCustomRangeChange = (dates: any) => {
    setFilters({
      ...filters,
      preset: 'custom',
      start_date: dates && dates[0] ? dates[0].format('YYYY-MM-DD') : filters.start_date,
      end_date: dates && dates[1] ? dates[1].format('YYYY-MM-DD') : filters.end_date,
    })
  }

  return (
    <div className='p-6'>
      <Row gutter={[16, 16]} align='bottom'>
        <Col>
          <Radio.Group value={filters.preset} onChange={(e) => applyPreset(e.target.value)} optionType='button'>
            <Radio.Button value='today'>{t('Today')}</Radio.Button>
            <Radio.Button value='week'>{t('This Week')}</Radio.Button>
            <Radio.Button value='month'>{t('This Month')}</Radio.Button>
          </Radio.Group>
        </Col>
        <Col>
          <label className='form-label d-block'>{t('Custom Range')}</label>
          <RangePicker
            value={[dayjs(filters.start_date), dayjs(filters.end_date)]}
            onChange={handleCustomRangeChange}
          />
        </Col>
        <Col flex='auto' className='d-flex justify-content-end'>
          <Button type='primary' onClick={handlePreview} loading={loading}>
            {t('Refresh')}
          </Button>
        </Col>
      </Row>
    </div>
  )
}

export default React.memo(HospitalDashboardFilter)

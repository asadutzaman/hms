import React, {FC} from 'react'
import {Button, Col, DatePicker, Input, Row, Spin} from 'antd'
import dayjs from 'dayjs'
import {DepartmentSelect} from 'src/app/components/Dropdown'
import {useLang} from 'src/app/hooks/useLang'

const {RangePicker} = DatePicker

const DailyCollectionListFilter: FC<any> = (props) => {
  const {filters, setFilters, loading, exportLoading, handlePreview, handleExport} = props
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
        <Col span={6}>
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
        <Col span={5}>
          <label className='form-label'>{t('Department')}</label>
          <DepartmentSelect
            departmentId={filters.department_id}
            allowClear
            placeholder={t('All Departments')}
            onSelect={(value: any) => setFilters({...filters, department_id: value})}
            onChange={(value: any) => setFilters({...filters, department_id: value})}
          />
        </Col>
        <Col span={5}>
          <label className='form-label'>{t('Doctor ID')}</label>
          <Input
            type='number'
            placeholder={t('All Doctors')}
            value={filters.doctor_id || undefined}
            onChange={(e) => setFilters({...filters, doctor_id: e.target.value || null})}
          />
        </Col>
        <Col span={8} className='d-flex justify-content-end'>
          <Button type='primary' onClick={handlePreview} className='me-3' disabled={loading}>
            {t('Preview')}
          </Button>
          <Button onClick={handleExport} disabled={exportLoading}>
            <Spin spinning={exportLoading} style={{paddingRight: exportLoading ? 8 : 0}} />
            {t('Export as XLS')}
          </Button>
        </Col>
      </Row>
    </div>
  )
}

export default React.memo(DailyCollectionListFilter)

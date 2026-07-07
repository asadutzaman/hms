import React, {FC, useEffect, useState} from 'react'
import {Card, Select, Spin, Timeline, Typography} from 'antd'
import {PatientPortalApi} from 'src/app/api'

const {Title} = Typography
const {Option} = Select

const typeOptions = [
  {value: '', label: 'All Events'},
  {value: 'opd_visit', label: 'OPD Visit'},
  {value: 'ipd_admission', label: 'IPD Admission'},
  {value: 'er_visit', label: 'Emergency Visit'},
  {value: 'lab_order', label: 'Lab Order'},
  {value: 'radiology_order', label: 'Radiology Order'},
  {value: 'prescription', label: 'Prescription'},
]

const PatientTimelineController: FC = () => {
  const [loading, setLoading] = useState<boolean>(true)
  const [events, setEvents] = useState<any[]>([])
  const [type, setType] = useState<string>('')

  const loadTimeline = (filterType: string) => {
    setLoading(true)
    PatientPortalApi.getTimeline(filterType ? {type: filterType} : {})
      .then((res: any) => {
        setEvents(res?.data?.timeline || [])
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }

  useEffect(() => {
    loadTimeline('')
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const handleTypeChange = (value: string) => {
    setType(value)
    loadTimeline(value)
  }

  const describeEvent = (event: any): string => {
    switch (event.type) {
      case 'opd_visit':
        return `OPD Visit — ${event.reference_no || ''}`
      case 'ipd_admission':
        return `IPD Admission — ${event.ward_name || ''} bed ${event.bed_number || ''}`
      case 'er_visit':
        return `Emergency Visit — ${event.chief_complaint || ''}`
      default:
        return `${event.type} — ${event.reference_no || event.id || ''}`
    }
  }

  return (
    <Spin spinning={loading}>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={3} className='mb-0'>
          Visit History
        </Title>
        <Select value={type} onChange={handleTypeChange} style={{width: 220}}>
          {typeOptions.map((opt) => (
            <Option key={opt.value} value={opt.value}>
              {opt.label}
            </Option>
          ))}
        </Select>
      </div>

      <Card>
        {events.length === 0 ? (
          <p className='text-muted text-center mb-0'>No events found.</p>
        ) : (
          <Timeline
            items={events.map((event: any, index: number) => ({
              key: index,
              children: (
                <div>
                  <strong>{describeEvent(event)}</strong>
                  <div className='text-muted fs-7'>{event.date}</div>
                  {event.status && <div className='text-capitalize fs-7'>Status: {event.status}</div>}
                </div>
              ),
            }))}
          />
        )}
      </Card>
    </Spin>
  )
}

export default PatientTimelineController

import React, {FC, useEffect, useState} from 'react'
import {Collapse, Descriptions, Empty, Spin, Tag} from 'antd'
import {PatientHistoryApi} from 'src/app/api'
import {DateTimeUtils} from 'src/app/utils'

interface PatientTimelinePanelProps {
  patientId: any
}

const typeMeta: Record<string, {label: string; color: string}> = {
  opd_visit: {label: 'OPD Visit', color: 'blue'},
  ipd_admission: {label: 'IPD Admission', color: 'purple'},
  er_visit: {label: 'Emergency Visit', color: 'red'},
}

// Unified read-only timeline across OPD visits, IPD admissions, and ER
// visits for a patient (F-16-01) — one aggregating backend call, rendered
// as a single chronological feed with a type badge per entry.
const PatientTimelinePanel: FC<PatientTimelinePanelProps> = ({patientId}) => {
  const [loading, setLoading] = useState(false)
  const [timeline, setTimeline] = useState<any[]>([])

  useEffect(() => {
    if (!patientId) return
    setLoading(true)
    PatientHistoryApi.timeline(patientId)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setTimeline(data.timeline || [])
      })
      .catch(() => setTimeline([]))
      .finally(() => setLoading(false))
  }, [patientId])

  if (loading) return <Spin />
  if (!timeline.length) return <Empty description='No clinical history yet' />

  const renderEntryDetail = (entry: any) => {
    if (entry.type === 'opd_visit') {
      return (
        <Descriptions bordered size='small' column={1}>
          <Descriptions.Item label='Doctor'>{entry.doctor_name || '-'}</Descriptions.Item>
          <Descriptions.Item label='Department'>{entry.department_name || '-'}</Descriptions.Item>
          <Descriptions.Item label='Chief Complaint'>{entry.chief_complaint || '-'}</Descriptions.Item>
          <Descriptions.Item label='Diagnoses'>
            {entry.diagnoses?.length
              ? entry.diagnoses.map((d: any) => (
                  <Tag key={d.id} color={d.is_primary ? 'red' : 'default'}>
                    {d.diagnosis_name || d.icd10_description}
                  </Tag>
                ))
              : '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Prescription'>
            {entry.prescription?.items?.length
              ? entry.prescription.items.map((it: any) => (
                  <Tag key={it.id}>
                    {it.drug_name} {it.strength} {it.frequency}
                  </Tag>
                ))
              : '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Investigations'>
            {entry.investigation_orders?.length ? `${entry.investigation_orders.length} order(s)` : '-'}
          </Descriptions.Item>
        </Descriptions>
      )
    }

    if (entry.type === 'ipd_admission') {
      return (
        <Descriptions bordered size='small' column={1}>
          <Descriptions.Item label='Doctor'>{entry.doctor_name || '-'}</Descriptions.Item>
          <Descriptions.Item label='Ward / Bed'>{entry.ward_name} / {entry.bed_number}</Descriptions.Item>
          <Descriptions.Item label='Diagnosis'>{entry.diagnosis || '-'}</Descriptions.Item>
          <Descriptions.Item label='Discharge Date'>
            {entry.discharge_date ? DateTimeUtils.formatDateTimeA(entry.discharge_date) : '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Medications'>
            {entry.medication_orders?.length
              ? entry.medication_orders.map((m: any) => <Tag key={m.id}>{m.drug_name}</Tag>)
              : '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Bill'>
            {entry.bill ? `${entry.bill.bill_no} — ${entry.bill.bill_status}` : '-'}
          </Descriptions.Item>
        </Descriptions>
      )
    }

    if (entry.type === 'er_visit') {
      return (
        <Descriptions bordered size='small' column={1}>
          <Descriptions.Item label='Chief Complaint'>{entry.chief_complaint}</Descriptions.Item>
          <Descriptions.Item label='Disposition'>{entry.disposition || '-'}</Descriptions.Item>
          <Descriptions.Item label='Triage'>
            {entry.triages?.length ? `Level ${entry.triages[0].triage_level}` : 'Not triaged'}
          </Descriptions.Item>
        </Descriptions>
      )
    }

    return null
  }

  return (
    <Collapse
      items={timeline.map((entry, idx) => {
        const meta = typeMeta[entry.type] || {label: entry.type, color: 'default'}
        return {
          key: entry.type + '-' + entry.id + '-' + idx,
          label: (
            <span>
              <Tag color={meta.color}>{meta.label}</Tag>
              {entry.reference_no} — {DateTimeUtils.formatDateTimeA(entry.date)}{' '}
              {entry.status && <Tag className='text-capitalize'>{entry.status.replace('_', ' ')}</Tag>}
            </span>
          ),
          children: renderEntryDetail(entry),
        }
      })}
    />
  )
}

export default PatientTimelinePanel

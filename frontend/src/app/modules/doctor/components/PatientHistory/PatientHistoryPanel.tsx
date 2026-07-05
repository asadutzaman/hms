import React, {FC, useEffect, useState} from 'react'
import {Collapse, Descriptions, Empty, Spin, Tag} from 'antd'
import {DoctorPortalApi} from 'src/app/api'

interface PatientHistoryPanelProps {
  patientId: any
}

// Read-only timeline of a patient's past OPD visits (diagnoses +
// prescriptions), reused across the Doctor Portal wherever a doctor needs
// prior-visit context (e.g. from the daily appointment list or dashboard).
const PatientHistoryPanel: FC<PatientHistoryPanelProps> = ({patientId}) => {
  const [loading, setLoading] = useState(false)
  const [visits, setVisits] = useState<any[]>([])

  useEffect(() => {
    if (!patientId) return
    setLoading(true)
    DoctorPortalApi.patientHistory(patientId)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setVisits(data.visits || [])
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }, [patientId])

  if (loading) return <Spin />
  if (!visits.length) return <Empty description='No previous visits' />

  return (
    <Collapse
      items={visits.map((visit) => ({
        key: visit.id,
        label: `${visit.visit_date} — ${visit.opd_no} (${visit.status})`,
        children: (
          <>
            <Descriptions bordered column={1} size='small' className='mb-4'>
              <Descriptions.Item label='Chief Complaint'>
                {visit.chief_complaint || '-'}
              </Descriptions.Item>
              <Descriptions.Item label='Diagnoses'>
                {visit.diagnoses?.length
                  ? visit.diagnoses.map((d: any) => (
                      <Tag key={d.id} color={d.is_primary ? 'red' : 'default'}>
                        {d.diagnosis_name || d.icd10_description}
                      </Tag>
                    ))
                  : '-'}
              </Descriptions.Item>
              <Descriptions.Item label='Prescription'>
                {visit.prescription?.items?.length
                  ? visit.prescription.items.map((it: any) => (
                      <Tag key={it.id}>
                        {it.drug_name} {it.strength} {it.frequency}
                      </Tag>
                    ))
                  : '-'}
              </Descriptions.Item>
            </Descriptions>
          </>
        ),
      }))}
    />
  )
}

export default PatientHistoryPanel

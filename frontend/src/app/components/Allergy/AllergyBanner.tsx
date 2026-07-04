import React, {FC, useEffect, useState} from 'react'
import {Alert, Tag} from 'antd'
import {WarningFilled} from '@ant-design/icons'
import {PatientApi} from 'src/app/api'

const severityColor: Record<string, string> = {
  severe: 'red',
  moderate: 'orange',
  mild: 'gold',
}

interface AllergyBannerProps {
  patientId: any
}

// Compact red alert surfaced wherever a doctor is about to prescribe, so a
// known allergy is visible without needing to open the patient's full record.
const AllergyBanner: FC<AllergyBannerProps> = ({patientId}) => {
  const [allergies, setAllergies] = useState<any[]>([])

  useEffect(() => {
    if (!patientId) return
    let mounted = true
    PatientApi.allergies(patientId)
      .then((res: any) => {
        if (!mounted) return
        const data = res?.data?.data ?? res?.data ?? []
        setAllergies(Array.isArray(data) ? data : [])
      })
      .catch(() => mounted && setAllergies([]))
    return () => {
      mounted = false
    }
  }, [patientId])

  if (!allergies.length) return null

  return (
    <Alert
      type='error'
      showIcon
      icon={<WarningFilled />}
      className='mb-6'
      message='Known Allergies'
      description={
        <div className='d-flex flex-wrap gap-2 mt-1'>
          {allergies.map((a) => (
            <Tag color={severityColor[a.severity] || 'red'} key={a.id}>
              {a.allergen_name}
              {a.reaction_type ? ` — ${a.reaction_type}` : ''} ({a.severity})
            </Tag>
          ))}
        </div>
      }
    />
  )
}

export default AllergyBanner

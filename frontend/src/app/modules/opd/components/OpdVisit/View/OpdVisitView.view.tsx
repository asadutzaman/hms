import React, {FC} from 'react'
import {Badge, Descriptions, Tag, Divider} from 'antd'
import {DateTimeUtils} from 'src/app/utils'

const statusColor = (status: string): string => {
  switch (status) {
    case 'scheduled':
      return 'blue'
    case 'confirmed':
      return 'cyan'
    case 'checked_in':
      return 'orange'
    case 'in_consultation':
      return 'gold'
    case 'completed':
      return 'green'
    case 'cancelled':
      return 'red'
    case 'no_show':
      return 'volcano'
    case 'rescheduled':
      return 'purple'
    default:
      return 'default'
  }
}

const OpdVisitView: FC<any> = ({itemData}) => {
  if (!itemData || !itemData.id) {
    return <div className='p-6'>No OpdVisit selected.</div>
  }

  return (
    <div className='view-page-content p-6'>
      {/* ============= HEADER ============= */}
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <div>
          <h3 className='mb-1'>{itemData.OpdVisit_no}</h3>
          <div className='text-muted fs-7'>
            {itemData.OpdVisit_date
              ? DateTimeUtils.formatDate(itemData.OpdVisit_date)
              : ''}{' '}
            •{' '}
            {itemData.start_time && itemData.end_time
              ? `${itemData.start_time} - ${itemData.end_time}`
              : ''}
          </div>
        </div>
        <div>
          <Badge color={statusColor(itemData.status)} className='text-capitalize fs-6'>
            {(itemData.status || 'unknown').replace('_', ' ')}
          </Badge>
        </div>
      </div>

      {/* ============= OpdVisit INFO ============= */}
      <Descriptions
        title='OpdVisit Information'
        bordered
        column={2}
        size='small'
        className='mb-6'
      >
        <Descriptions.Item label='OpdVisit #'>
          {itemData.OpdVisit_no}
        </Descriptions.Item>
        <Descriptions.Item label='Token'>
          {itemData.token_number || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Doctor'>
          {itemData.doctor?.name_en ||
            itemData.doctor?.name ||
            itemData.doctor_name ||
            '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Department'>
          {itemData.department?.name || itemData.department_name || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Chamber'>
          {itemData.chamber?.name || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Schedule'>
          {itemData.schedule?.name || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Date'>
          {itemData.OpdVisit_date
            ? DateTimeUtils.formatDate(itemData.OpdVisit_date)
            : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Time'>
          {itemData.start_time && itemData.end_time
            ? `${itemData.start_time} - ${itemData.end_time}`
            : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Duration'>
          {itemData.duration_minutes ? `${itemData.duration_minutes} min` : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Type'>
          {itemData.OpdVisit_type ? (
            <span className='text-capitalize'>
              {itemData.OpdVisit_type.replace('_', ' ')}
            </span>
          ) : (
            '-'
          )}
        </Descriptions.Item>
        <Descriptions.Item label='Source'>
          {itemData.source ? (
            <span className='text-capitalize'>{itemData.source.replace('_', ' ')}</span>
          ) : (
            '-'
          )}
        </Descriptions.Item>
        <Descriptions.Item label='Consultation Mode'>
          {itemData.consultation_mode ? (
            <span className='text-capitalize'>
              {itemData.consultation_mode.replace('_', ' ')}
            </span>
          ) : (
            '-'
          )}
        </Descriptions.Item>
        <Descriptions.Item label='Fee'>
          {itemData.consultation_fee != null
            ? `${itemData.currency || 'BDT'} ${itemData.consultation_fee}`
            : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Follow-up Fee'>
          {itemData.follow_up_fee != null
            ? `${itemData.currency || 'BDT'} ${itemData.follow_up_fee}`
            : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Status'>
          <Badge color={statusColor(itemData.status)} className='text-capitalize'>
            {(itemData.status || 'unknown').replace('_', ' ')}
          </Badge>
        </Descriptions.Item>
      </Descriptions>

      {/* ============= PATIENT INFO ============= */}
      <Descriptions
        title='Patient Information'
        bordered
        column={2}
        size='small'
        className='mb-6'
      >
        <Descriptions.Item label='Patient Name'>
          {itemData.patient?.full_name || itemData.patient_name || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='MRN'>
          {itemData.patient?.mrn || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Patient No'>
          {itemData.patient?.patient_no || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Phone'>
          {itemData.patient?.primary_phone || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Gender'>
          {itemData.patient?.gender ? (
            <span className='text-capitalize'>{itemData.patient.gender}</span>
          ) : (
            '-'
          )}
        </Descriptions.Item>
        <Descriptions.Item label='Date of Birth'>
          {itemData.patient?.date_of_birth
            ? DateTimeUtils.formatDate(itemData.patient.date_of_birth)
            : '-'}
        </Descriptions.Item>
      </Descriptions>

      {/* ============= NOTES ============= */}
      <Descriptions
        title='Clinical Notes'
        bordered
        column={1}
        size='small'
        className='mb-6'
      >
        <Descriptions.Item label='Reason for Visit'>
          {itemData.reason || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Symptoms'>
          {itemData.symptoms || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Patient Notes'>
          {itemData.notes || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Internal Notes'>
          {itemData.internal_notes || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Referral Notes'>
          {itemData.referral_notes || '-'}
        </Descriptions.Item>
      </Descriptions>

      {/* ============= META ============= */}
      <Divider />
      <Descriptions column={2} size='small' className='text-muted fs-7'>
        <Descriptions.Item label='Created By'>
          {itemData.created_by_name || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Created At'>
          {itemData.created_at ? DateTimeUtils.formatDateTime(itemData.created_at) : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Updated By'>
          {itemData.updated_by_name || '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Updated At'>
          {itemData.updated_at ? DateTimeUtils.formatDateTime(itemData.updated_at) : '-'}
        </Descriptions.Item>
      </Descriptions>

      {/* ============= AUDIT TRAIL ============= */}
      {itemData.audit_logs && itemData.audit_logs.length > 0 && (
        <>
          <Divider plain>Audit Trail</Divider>
          {itemData.audit_logs.map((log: any) => (
            <div key={log.id} className='mb-2'>
              <Tag color='default'>{log.action}</Tag>
              <span className='text-muted fs-7 me-2'>
                {log.created_at ? DateTimeUtils.formatDateTime(log.created_at) : ''}
              </span>
              <span>by {log.user_name || '-'}</span>
              {log.notes && (
                <div className='text-muted fs-7 ms-5'>{log.notes}</div>
              )}
            </div>
          ))}
        </>
      )}
    </div>
  )
}

export default React.memo(OpdVisitView)

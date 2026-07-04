import React, {FC, useState} from 'react'
import {Badge, Descriptions, Tag, Divider, Button, Space, Modal, Form, Input, Tooltip, notification} from 'antd'
import {StopOutlined, CalendarOutlined} from '@ant-design/icons'
import {DateTimeUtils} from 'src/app/utils'
import {AppointmentApi} from 'src/app/api'
import AuditLogPanel from 'src/app/components/AuditLog/AuditLogPanel'

const {TextArea} = Input

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

// Backend allows cancel/reschedule up to 2h before the appointment; mirror that
// here so the buttons are disabled ahead of the API rejecting the request.
const isWithinModifyWindow = (itemData: any): boolean => {
  if (!itemData?.appointment_date) return true
  const dateTime = new Date(`${itemData.appointment_date}T${itemData.start_time || '00:00'}`)
  if (isNaN(dateTime.getTime())) return true
  return dateTime.getTime() - Date.now() > 2 * 60 * 60 * 1000
}

const AppointmentView: FC<any> = ({itemData, handleCallbackFunc}) => {
  const [cancelForm] = Form.useForm()
  const [rescheduleForm] = Form.useForm()
  const [cancelModalOpen, setCancelModalOpen] = useState(false)
  const [rescheduleModalOpen, setRescheduleModalOpen] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  if (!itemData || !itemData.id) {
    return <div className='p-6'>No appointment selected.</div>
  }

  const modifiable = !['completed', 'cancelled'].includes(itemData.status)
  const withinWindow = isWithinModifyWindow(itemData)

  const notifyReload = () => {
    handleCallbackFunc?.('singleAction', 'reloadView')
    handleCallbackFunc?.('singleAction', 'reloadListing')
  }

  const handleCancel = async (values: any) => {
    setSubmitting(true)
    try {
      await AppointmentApi.cancel(itemData.id, {cancellation_reason: values.cancellation_reason})
      notification.success({message: 'Appointment cancelled'})
      setCancelModalOpen(false)
      cancelForm.resetFields()
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to cancel appointment',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const handleReschedule = async (values: any) => {
    setSubmitting(true)
    try {
      await AppointmentApi.reschedule(itemData.id, {
        appointment_date: values.appointment_date,
        appointment_time: values.appointment_time,
        reason: values.reason,
      })
      notification.success({message: 'Appointment rescheduled'})
      setRescheduleModalOpen(false)
      rescheduleForm.resetFields()
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to reschedule appointment',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className='view-page-content p-6'>
      {/* ============= HEADER ============= */}
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <div>
          <h3 className='mb-1'>{itemData.appointment_no}</h3>
          <div className='text-muted fs-7'>
            {itemData.appointment_date
              ? DateTimeUtils.formatDate(itemData.appointment_date)
              : ''}{' '}
            •{' '}
            {itemData.start_time && itemData.end_time
              ? `${itemData.start_time} - ${itemData.end_time}`
              : ''}
          </div>
        </div>
        <div className='d-flex align-items-center gap-3'>
          <Badge color={statusColor(itemData.status)} className='text-capitalize fs-6'>
            {(itemData.status || 'unknown').replace('_', ' ')}
          </Badge>
          {modifiable && (
            <Space>
              <Tooltip title={!withinWindow ? 'Cannot reschedule within 2 hours of the appointment' : ''}>
                <Button
                  size='small'
                  icon={<CalendarOutlined />}
                  disabled={!withinWindow}
                  onClick={() => setRescheduleModalOpen(true)}
                >
                  Reschedule
                </Button>
              </Tooltip>
              <Tooltip title={!withinWindow ? 'Cannot cancel within 2 hours of the appointment' : ''}>
                <Button
                  size='small'
                  danger
                  icon={<StopOutlined />}
                  disabled={!withinWindow}
                  onClick={() => setCancelModalOpen(true)}
                >
                  Cancel
                </Button>
              </Tooltip>
            </Space>
          )}
        </div>
      </div>

      {itemData.reschedule_count > 0 && (
        <Tag color='purple' className='mb-4'>
          Rescheduled {itemData.reschedule_count} time(s)
          {itemData.rescheduled_from_id ? ` (from appointment #${itemData.rescheduled_from_id})` : ''}
        </Tag>
      )}

      {/* ============= APPOINTMENT INFO ============= */}
      <Descriptions
        title='Appointment Information'
        bordered
        column={2}
        size='small'
        className='mb-6'
      >
        <Descriptions.Item label='Appointment #'>
          {itemData.appointment_no}
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
          {itemData.appointment_date
            ? DateTimeUtils.formatDate(itemData.appointment_date)
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
          {itemData.appointment_type ? (
            <span className='text-capitalize'>
              {itemData.appointment_type.replace('_', ' ')}
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
      <Divider plain>Audit Trail</Divider>
      <AuditLogPanel fetchFn={() => AppointmentApi.auditLog(itemData.id)} reloadKey={itemData.id} />

      {/* ============= CANCEL MODAL ============= */}
      <Modal
        title='Cancel Appointment'
        open={cancelModalOpen}
        onCancel={() => setCancelModalOpen(false)}
        onOk={() => cancelForm.submit()}
        confirmLoading={submitting}
        okText='Confirm Cancellation'
        okButtonProps={{danger: true}}
      >
        <Form form={cancelForm} layout='vertical' onFinish={handleCancel}>
          <Form.Item
            name='cancellation_reason'
            label='Cancellation Reason'
            rules={[{max: 500, message: 'Maximum 500 characters'}]}
          >
            <TextArea rows={3} placeholder='Optional reason for cancellation' />
          </Form.Item>
        </Form>
      </Modal>

      {/* ============= RESCHEDULE MODAL ============= */}
      <Modal
        title='Reschedule Appointment'
        open={rescheduleModalOpen}
        onCancel={() => setRescheduleModalOpen(false)}
        onOk={() => rescheduleForm.submit()}
        confirmLoading={submitting}
        okText='Confirm Reschedule'
      >
        <Form form={rescheduleForm} layout='vertical' onFinish={handleReschedule}>
          <Form.Item
            name='appointment_date'
            label='New Date'
            rules={[{required: true, message: 'Please select a date'}]}
          >
            <Input type='date' />
          </Form.Item>
          <Form.Item
            name='appointment_time'
            label='New Time'
            rules={[{required: true, message: 'Please select a time'}]}
          >
            <Input type='time' />
          </Form.Item>
          <Form.Item name='reason' label='Reason' rules={[{max: 500, message: 'Maximum 500 characters'}]}>
            <TextArea rows={3} placeholder='Optional reason for rescheduling' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default React.memo(AppointmentView)

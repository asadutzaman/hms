import React, {FC, useState} from 'react'
import {Modal, Tag, Button, InputNumber, Input, notification, Popconfirm} from 'antd'
import {PreAuthorizationApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input

const STATUS_COLOR: Record<string, string> = {
  submitted: 'default',
  under_review: 'gold',
  approved: 'green',
  rejected: 'red',
  expired: 'default',
  cancelled: 'default',
}

interface PreAuthorizationDetailModalProps {
  visible: boolean
  onClose: () => void
  onChanged: () => void
  record: any
}

const PreAuthorizationDetailModal: FC<PreAuthorizationDetailModalProps> = ({visible, onClose, onChanged, record}) => {
  const {t} = useLang()
  const [busy, setBusy] = useState(false)
  const [approvedAmount, setApprovedAmount] = useState<any>(record?.estimated_amount)
  const [notes, setNotes] = useState('')

  if (!record) return null

  const handleMarkUnderReview = async () => {
    setBusy(true)
    try {
      await PreAuthorizationApi.markUnderReview(record.id)
      notification.success({message: t('Marked as under review')})
      onChanged()
      onClose()
    } catch (e: any) {
      notification.error({message: t('Failed to update status')})
    } finally {
      setBusy(false)
    }
  }

  const handleApprove = async () => {
    setBusy(true)
    try {
      await PreAuthorizationApi.approve(record.id, {approved_amount: approvedAmount, notes})
      notification.success({message: t('Pre-authorization approved')})
      onChanged()
      onClose()
    } catch (e: any) {
      notification.error({message: t('Failed to approve'), description: e?.response?.data?.message})
    } finally {
      setBusy(false)
    }
  }

  const handleReject = async () => {
    if (!notes) {
      notification.warning({message: t('Enter a reason for rejection')})
      return
    }
    setBusy(true)
    try {
      await PreAuthorizationApi.reject(record.id, {notes})
      notification.success({message: t('Pre-authorization rejected')})
      onChanged()
      onClose()
    } catch (e: any) {
      notification.error({message: t('Failed to reject')})
    } finally {
      setBusy(false)
    }
  }

  const isActionable = ['submitted', 'under_review'].includes(record.pa_status)

  return (
    <Modal title={t('Pre-Authorization') + ' — ' + record.pa_no} open={visible} onCancel={onClose} footer={null} width={600}>
      <div className='mb-4'>
        <Tag color={STATUS_COLOR[record.pa_status] || 'default'} className='fs-6 mb-3'>
          {record.pa_status_label}
        </Tag>
        <table className='table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3'>
          <tbody>
            <tr>
              <td width='35%'>{t('Patient')}</td>
              <td>{record.patient_name} (MRN {record.mrn})</td>
            </tr>
            <tr>
              <td>{t('Insurance Company')}</td>
              <td>{record.insurance_company_name}</td>
            </tr>
            <tr>
              <td>{t('Scheme')}</td>
              <td>{record.insurance_scheme_name || '-'}</td>
            </tr>
            <tr>
              <td>{t('Policy Number')}</td>
              <td>{record.policy_number || '-'}</td>
            </tr>
            <tr>
              <td>{t('Estimated Amount')}</td>
              <td>{record.estimated_amount}</td>
            </tr>
            <tr>
              <td>{t('Approved Amount')}</td>
              <td>{record.approved_amount ?? '-'}</td>
            </tr>
            <tr>
              <td>{t('Diagnosis')}</td>
              <td>{record.diagnosis || '-'}</td>
            </tr>
            <tr>
              <td>{t('Treatment Plan')}</td>
              <td>{record.treatment_plan || '-'}</td>
            </tr>
            {record.response_notes && (
              <tr>
                <td>{t('Response Notes')}</td>
                <td>{record.response_notes}</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {isActionable && (
        <div className='border rounded p-4'>
          {record.pa_status === 'submitted' && (
            <Button className='mb-3' onClick={handleMarkUnderReview} loading={busy}>
              {t('Mark Under Review')}
            </Button>
          )}
          <div className='mb-3'>
            <label className='form-label'>{t('Approved Amount')}</label>
            <InputNumber
              min={0}
              precision={2}
              style={{width: '100%'}}
              value={approvedAmount}
              onChange={(v) => setApprovedAmount(v)}
            />
          </div>
          <div className='mb-3'>
            <label className='form-label'>{t('Notes')}</label>
            <TextArea rows={2} value={notes} onChange={(e) => setNotes(e.target.value)} />
          </div>
          <div className='d-flex justify-content-end'>
            <Popconfirm title={t('Reject this pre-authorization request?')} onConfirm={handleReject}>
              <Button danger className='me-2' loading={busy}>
                {t('Reject')}
              </Button>
            </Popconfirm>
            <Button type='primary' onClick={handleApprove} loading={busy}>
              {t('Approve')}
            </Button>
          </div>
        </div>
      )}
    </Modal>
  )
}

export default PreAuthorizationDetailModal

import React, {FC, useEffect, useState} from 'react'
import {Modal, Input, Select, notification, Spin} from 'antd'
import {RadiologyReportTemplateApi, RadiologyReportApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input
const {Option} = Select

interface EnterReportModalProps {
  visible: boolean
  onClose: () => void
  onSaved: () => void
  orderItem: any
}

const EnterReportModal: FC<EnterReportModalProps> = (props) => {
  const {visible, onClose, onSaved, orderItem} = props
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [saving, setSaving] = useState(false)
  const [templates, setTemplates] = useState<any[]>([])
  const [templateId, setTemplateId] = useState<any>(null)
  const [findings, setFindings] = useState('')
  const [impression, setImpression] = useState('')

  useEffect(() => {
    if (visible && orderItem) {
      setLoading(true)
      RadiologyReportTemplateApi.list({$filter: `modality='${orderItem.modality_snapshot}'`, $top: 50})
        .then((res: any) => setTemplates(res?.data?.results || []))
        .catch(() => setTemplates([]))
        .finally(() => setLoading(false))

      const report = orderItem.report
      setFindings(report?.findings || '')
      setImpression(report?.impression || '')
      setTemplateId(report?.radiology_report_template_id || null)
    }
  }, [visible, orderItem])

  const applyTemplate = (id: any) => {
    setTemplateId(id)
    const template = templates.find((t) => t.id === id)
    if (template) {
      setFindings(template.findings_template || '')
      setImpression(template.impression_template || '')
    }
  }

  const handleSaveDraft = async () => {
    setSaving(true)
    try {
      await RadiologyReportApi.saveDraft(orderItem.id, {
        radiology_report_template_id: templateId,
        findings,
        impression,
      })
      notification.success({message: t('Report draft saved')})
      onSaved()
      onClose()
    } catch (e: any) {
      notification.error({message: t('Failed to save draft'), description: e?.response?.data?.message})
    } finally {
      setSaving(false)
    }
  }

  const handleFinalize = async () => {
    setSaving(true)
    try {
      await RadiologyReportApi.saveDraft(orderItem.id, {
        radiology_report_template_id: templateId,
        findings,
        impression,
      })
      await RadiologyReportApi.finalize(orderItem.id)
      notification.success({message: t('Report finalized')})
      onSaved()
      onClose()
    } catch (e: any) {
      notification.error({message: t('Failed to finalize report'), description: e?.response?.data?.message})
    } finally {
      setSaving(false)
    }
  }

  return (
    <Modal
      title={t('Enter Report') + ' — ' + (orderItem?.test_name_snapshot || '')}
      open={visible}
      onCancel={onClose}
      footer={[
        <button key='cancel' className='btn btn-light me-2' onClick={onClose}>
          {t('Cancel')}
        </button>,
        <button key='draft' className='btn btn-light-primary me-2' disabled={saving} onClick={handleSaveDraft}>
          {t('Save Draft')}
        </button>,
        <button key='finalize' className='btn btn-primary' disabled={saving} onClick={handleFinalize}>
          {t('Finalize Report')}
        </button>,
      ]}
      width={700}
      destroyOnClose
    >
      <Spin spinning={loading}>
        <div className='mb-3'>
          <label className='form-label'>{t('Prefill from Template')}</label>
          <Select
            style={{width: '100%'}}
            placeholder={t('Select a template (optional)')}
            allowClear
            value={templateId}
            onChange={applyTemplate}
          >
            {templates.map((tpl: any) => (
              <Option key={tpl.id} value={tpl.id}>
                {tpl.name}
              </Option>
            ))}
          </Select>
        </div>
        <div className='mb-3'>
          <label className='form-label'>{t('Findings')}</label>
          <TextArea rows={6} value={findings} onChange={(e) => setFindings(e.target.value)} />
        </div>
        <div className='mb-3'>
          <label className='form-label'>{t('Impression')}</label>
          <TextArea rows={3} value={impression} onChange={(e) => setImpression(e.target.value)} />
        </div>
      </Spin>
    </Modal>
  )
}

export default EnterReportModal

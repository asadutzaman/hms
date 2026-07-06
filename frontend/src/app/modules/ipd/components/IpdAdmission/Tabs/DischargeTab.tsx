import React, {FC, useEffect, useState} from 'react'
import {Card, Form, Input, Select, DatePicker, TimePicker, Button, Descriptions, Tag, Empty, notification, Space, Popconfirm, Alert} from 'antd'
import {IpdDischargeSummaryApi, IpdDeathCertificateApi} from 'src/app/api'
import {DateTimeUtils} from 'src/app/utils'

const {TextArea} = Input
const {Option} = Select

interface DischargeTabProps {
  admissionId: number
  admissionStatus: string
}

const DischargeTab: FC<DischargeTabProps> = ({admissionId, admissionStatus}) => {
  const [summary, setSummary] = useState<any>(null)
  const [certificate, setCertificate] = useState<any>(null)
  const [summaryForm] = Form.useForm()
  const [certificateForm] = Form.useForm()
  const [submitting, setSubmitting] = useState(false)

  const isDeceased = admissionStatus === 'deceased'
  const isDischargedLike = admissionStatus === 'discharged' || admissionStatus === 'dama'
  const isFinalized = isDeceased ? certificate?.is_finalized : summary?.is_finalized

  const loadSummary = () => {
    IpdDischargeSummaryApi.byAdmission(admissionId)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? null
        setSummary(data)
        if (data) summaryForm.setFieldsValue(data)
      })
      .catch(() => setSummary(null))
  }

  const loadCertificate = () => {
    IpdDeathCertificateApi.byAdmission(admissionId)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? null
        setCertificate(data)
        if (data) certificateForm.setFieldsValue(data)
      })
      .catch(() => setCertificate(null))
  }

  useEffect(() => {
    if (isDischargedLike) loadSummary()
    if (isDeceased) loadCertificate()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [admissionId, admissionStatus])

  const handleGenerateDraft = async () => {
    setSubmitting(true)
    try {
      const res: any = await IpdDischargeSummaryApi.generate(admissionId)
      setSummary(res?.data?.data ?? res?.data)
      summaryForm.setFieldsValue(res?.data?.data ?? res?.data)
      notification.success({message: 'Discharge summary draft generated'})
    } catch (e: any) {
      notification.error({message: 'Failed to generate draft', description: e?.response?.data?.message})
    } finally {
      setSubmitting(false)
    }
  }

  const handleSaveSummary = async (values: any) => {
    setSubmitting(true)
    try {
      const res: any = await IpdDischargeSummaryApi.update(summary.id, values)
      setSummary(res?.data?.data ?? res?.data)
      notification.success({message: 'Discharge summary saved'})
    } catch (e: any) {
      notification.error({message: 'Failed to save', description: e?.response?.data?.message})
    } finally {
      setSubmitting(false)
    }
  }

  const handleSignSummary = async () => {
    setSubmitting(true)
    try {
      const res: any = await IpdDischargeSummaryApi.sign(summary.id)
      setSummary(res?.data?.data ?? res?.data)
      notification.success({message: 'Discharge summary signed'})
    } catch (e: any) {
      notification.error({message: 'Failed to sign', description: e?.response?.data?.message})
    } finally {
      setSubmitting(false)
    }
  }

  const handleCreateCertificate = async (values: any) => {
    setSubmitting(true)
    try {
      const payload = {
        ...values,
        admission_id: admissionId,
        date_of_death: values.date_of_death?.format ? values.date_of_death.format('YYYY-MM-DD') : values.date_of_death,
        time_of_death: values.time_of_death?.format ? values.time_of_death.format('HH:mm:ss') : values.time_of_death,
      }
      const res: any = await IpdDeathCertificateApi.create(payload)
      setCertificate(res?.data?.data ?? res?.data)
      notification.success({message: 'Death certificate created'})
    } catch (e: any) {
      notification.error({message: 'Failed to create certificate', description: e?.response?.data?.message})
    } finally {
      setSubmitting(false)
    }
  }

  const handleSaveCertificate = async (values: any) => {
    setSubmitting(true)
    try {
      const res: any = await IpdDeathCertificateApi.update(certificate.id, values)
      setCertificate(res?.data?.data ?? res?.data)
      notification.success({message: 'Death certificate saved'})
    } catch (e: any) {
      notification.error({message: 'Failed to save', description: e?.response?.data?.message})
    } finally {
      setSubmitting(false)
    }
  }

  const handleCertify = async () => {
    setSubmitting(true)
    try {
      const res: any = await IpdDeathCertificateApi.certify(certificate.id)
      setCertificate(res?.data?.data ?? res?.data)
      notification.success({message: 'Death certificate certified'})
    } catch (e: any) {
      notification.error({message: 'Failed to certify', description: e?.response?.data?.message})
    } finally {
      setSubmitting(false)
    }
  }

  if (!isDischargedLike && !isDeceased) {
    return <Empty description='Discharge documentation becomes available once the patient is discharged or deceased.' />
  }

  if (isDeceased) {
    return (
      <div>
        <h5 className='mb-3'>Death Certificate</h5>
        {!certificate ? (
          <Card size='small'>
            <Form form={certificateForm} layout='vertical' onFinish={handleCreateCertificate} initialValues={{manner_of_death: 'natural'}}>
              <Space wrap>
                <Form.Item name='date_of_death' label='Date of Death' rules={[{required: true}]}>
                  <DatePicker />
                </Form.Item>
                <Form.Item name='time_of_death' label='Time of Death'>
                  <TimePicker />
                </Form.Item>
                <Form.Item name='manner_of_death' label='Manner of Death'>
                  <Select style={{width: 160}}>
                    {['natural', 'accident', 'suicide', 'homicide', 'undetermined'].map((m) => (
                      <Option key={m} value={m}>{m}</Option>
                    ))}
                  </Select>
                </Form.Item>
              </Space>
              <Form.Item name='immediate_cause' label='Immediate Cause of Death' rules={[{required: true, message: 'Required'}]}>
                <TextArea rows={2} />
              </Form.Item>
              <Form.Item name='antecedent_cause' label='Antecedent Cause'>
                <TextArea rows={2} />
              </Form.Item>
              <Form.Item name='underlying_cause' label='Underlying Cause'>
                <TextArea rows={2} />
              </Form.Item>
              <Form.Item name='other_significant_conditions' label='Other Significant Conditions'>
                <TextArea rows={2} />
              </Form.Item>
              <Button type='primary' htmlType='submit' loading={submitting}>
                Create Certificate
              </Button>
            </Form>
          </Card>
        ) : (
          <Card
            size='small'
            title={certificate.certificate_no}
            extra={
              <Space>
                <Tag color={certificate.is_finalized ? 'green' : 'gold'}>{certificate.is_finalized ? 'Certified' : 'Draft'}</Tag>
                {!certificate.is_finalized && (
                  <Popconfirm title='Certify this death certificate? It cannot be edited afterwards.' onConfirm={handleCertify}>
                    <Button size='small' type='primary'>
                      Certify
                    </Button>
                  </Popconfirm>
                )}
              </Space>
            }
          >
            {certificate.is_finalized ? (
              <Descriptions bordered size='small' column={1}>
                <Descriptions.Item label='Date/Time of Death'>{certificate.date_of_death} {certificate.time_of_death}</Descriptions.Item>
                <Descriptions.Item label='Immediate Cause'>{certificate.immediate_cause}</Descriptions.Item>
                <Descriptions.Item label='Antecedent Cause'>{certificate.antecedent_cause || '-'}</Descriptions.Item>
                <Descriptions.Item label='Underlying Cause'>{certificate.underlying_cause || '-'}</Descriptions.Item>
                <Descriptions.Item label='Manner of Death'>{certificate.manner_of_death}</Descriptions.Item>
                <Descriptions.Item label='Certified By'>{certificate.certified_by_name} at {DateTimeUtils.formatDateTimeA(certificate.certified_at)}</Descriptions.Item>
              </Descriptions>
            ) : (
              <Form form={certificateForm} layout='vertical' onFinish={handleSaveCertificate}>
                <Form.Item name='immediate_cause' label='Immediate Cause of Death' rules={[{required: true}]}>
                  <TextArea rows={2} />
                </Form.Item>
                <Form.Item name='antecedent_cause' label='Antecedent Cause'>
                  <TextArea rows={2} />
                </Form.Item>
                <Form.Item name='underlying_cause' label='Underlying Cause'>
                  <TextArea rows={2} />
                </Form.Item>
                <Button htmlType='submit' loading={submitting}>
                  Save
                </Button>
              </Form>
            )}
          </Card>
        )}
      </div>
    )
  }

  return (
    <div>
      <h5 className='mb-3'>Discharge Summary</h5>
      {!summary ? (
        <Button type='primary' loading={submitting} onClick={handleGenerateDraft}>
          Generate Draft
        </Button>
      ) : (
        <Card
          size='small'
          title={summary.summary_no}
          extra={
            <Space>
              <Tag color={summary.is_finalized ? 'green' : 'gold'}>{summary.is_finalized ? 'Signed' : 'Draft'}</Tag>
              {!summary.is_finalized && (
                <Popconfirm title='Sign this discharge summary? It cannot be edited afterwards.' onConfirm={handleSignSummary}>
                  <Button size='small' type='primary'>
                    Sign Off
                  </Button>
                </Popconfirm>
              )}
            </Space>
          }
        >
          {summary.is_finalized ? (
            <Descriptions bordered size='small' column={1}>
              <Descriptions.Item label='Admission Diagnosis'>{summary.admission_diagnosis || '-'}</Descriptions.Item>
              <Descriptions.Item label='Discharge Diagnosis'>{summary.discharge_diagnosis}</Descriptions.Item>
              <Descriptions.Item label='Hospital Course'>{summary.hospital_course || '-'}</Descriptions.Item>
              <Descriptions.Item label='Discharge Condition'>{summary.discharge_condition || '-'}</Descriptions.Item>
              <Descriptions.Item label='Follow-up Instructions'>{summary.follow_up_instructions || '-'}</Descriptions.Item>
              <Descriptions.Item label='Signed By'>{summary.signed_by_name} at {DateTimeUtils.formatDateTimeA(summary.signed_at)}</Descriptions.Item>
            </Descriptions>
          ) : (
            <Form form={summaryForm} layout='vertical' onFinish={handleSaveSummary}>
              {!summary.discharge_diagnosis && (
                <Alert type='info' showIcon className='mb-3' message='Discharge diagnosis is required before this can be signed.' />
              )}
              <Descriptions bordered size='small' column={1} className='mb-4'>
                <Descriptions.Item label='Admission Diagnosis'>{summary.admission_diagnosis || '-'}</Descriptions.Item>
              </Descriptions>
              <Form.Item name='discharge_diagnosis' label='Discharge Diagnosis' rules={[{required: true, message: 'Required'}]}>
                <TextArea rows={2} />
              </Form.Item>
              <Form.Item name='hospital_course' label='Hospital Course'>
                <TextArea rows={3} />
              </Form.Item>
              <Form.Item name='procedures_performed' label='Procedures Performed'>
                <TextArea rows={2} />
              </Form.Item>
              <Form.Item name='discharge_condition' label='Discharge Condition'>
                <Select allowClear style={{width: 200}}>
                  {['stable', 'improved', 'unchanged', 'deteriorated'].map((c) => (
                    <Option key={c} value={c}>{c}</Option>
                  ))}
                </Select>
              </Form.Item>
              <Form.Item name='follow_up_instructions' label='Follow-up Instructions'>
                <TextArea rows={2} />
              </Form.Item>
              <Form.Item name='discharge_advice' label='Discharge Advice'>
                <TextArea rows={2} />
              </Form.Item>
              <Button htmlType='submit' loading={submitting}>
                Save
              </Button>
            </Form>
          )}
        </Card>
      )}
    </div>
  )
}

export default DischargeTab

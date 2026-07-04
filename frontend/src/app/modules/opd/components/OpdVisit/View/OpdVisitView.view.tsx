import React, {FC, useState} from 'react'
import {
  Badge,
  Descriptions,
  Tag,
  Divider,
  Tabs,
  Button,
  Space,
  Modal,
  Form,
  InputNumber,
  Input,
  Select,
  Switch,
  Table,
  Empty,
  notification,
  Popconfirm,
  Row,
  Col,
} from 'antd'
import {
  PlusOutlined,
  EditOutlined,
  DeleteOutlined,
  PrinterOutlined,
  FileTextOutlined,
  CalendarOutlined,
  DollarOutlined,
  PercentageOutlined,
  CheckOutlined,
  CloseOutlined,
} from '@ant-design/icons'
import {DateTimeUtils} from 'src/app/utils'
import {OpdVisitApi, OpdVitalApi, OpdDiagnosisApi, OpdPrescriptionApi, OpdBillApi} from 'src/app/api'
import AuditLogPanel from 'src/app/components/AuditLog/AuditLogPanel'
import AllergyBanner from 'src/app/components/Allergy/AllergyBanner'
import {usePermissionContext} from 'src/app/hooks/context/usePermissionContext'

const {TextArea} = Input
const {Option} = Select

const statusColor = (status: string): string => {
  switch (status) {
    case 'waiting':
      return 'blue'
    case 'vitals_taken':
      return 'cyan'
    case 'in_consultation':
      return 'gold'
    case 'completed':
      return 'green'
    case 'billed':
      return 'purple'
    case 'closed':
      return 'default'
    case 'cancelled':
      return 'red'
    default:
      return 'default'
  }
}

const OpdVisitView: FC<any> = ({itemData, handleCallbackFunc}) => {
  const {hasPermission} = usePermissionContext()
  const [vitalModalOpen, setVitalModalOpen] = useState(false)
  const [diagnosisModalOpen, setDiagnosisModalOpen] = useState(false)
  const [prescriptionModalOpen, setPrescriptionModalOpen] = useState(false)
  const [followUpModalOpen, setFollowUpModalOpen] = useState(false)
  const [paymentModalOpen, setPaymentModalOpen] = useState(false)
  const [discountModalOpen, setDiscountModalOpen] = useState(false)
  const [submitting, setSubmitting] = useState(false)
  const [printingPdf, setPrintingPdf] = useState(false)
  const [printingReceipt, setPrintingReceipt] = useState(false)
  const [generatingBill, setGeneratingBill] = useState(false)
  const [vitalForm] = Form.useForm()
  const [diagnosisForm] = Form.useForm()
  const [prescriptionForm] = Form.useForm()
  const [followUpForm] = Form.useForm()
  const [paymentForm] = Form.useForm()
  const [discountForm] = Form.useForm()

  if (!itemData || !itemData.id) {
    return <div className='p-6'>No OPD visit selected.</div>
  }

  const isLocked = !!itemData.is_terminal
  const vitals = itemData.vitals
  const diagnoses: any[] = itemData.diagnoses || []
  const prescription = itemData.prescription
  const prescriptionItems: any[] = prescription?.items || []
  const bill = itemData.bill
  const billItems: any[] = bill?.items || []
  const billPayments: any[] = bill?.payments || []

  const notifyReload = () => {
    handleCallbackFunc?.('singleAction', 'reloadView')
    handleCallbackFunc?.('singleAction', 'reloadListing')
  }

  const openVitalModal = () => {
    vitalForm.setFieldsValue({
      systolic: vitals?.systolic,
      diastolic: vitals?.diastolic,
      pulse: vitals?.pulse,
      temperature: vitals?.temperature,
      spo2: vitals?.spo2,
      weight: vitals?.weight,
      height: vitals?.height,
      notes: vitals?.notes,
    })
    setVitalModalOpen(true)
  }

  const handleSaveVitals = async (values: any) => {
    setSubmitting(true)
    try {
      const payload = {
        opd_visit_id: itemData.id,
        patient_id: itemData.patient_id,
        bp_systolic: values.systolic,
        bp_diastolic: values.diastolic,
        pulse_bpm: values.pulse,
        temperature_c: values.temperature,
        spo2_pct: values.spo2,
        weight_kg: values.weight,
        height_cm: values.height,
        notes: values.notes,
        recorded_at: new Date().toISOString(),
      }
      if (vitals?.id) {
        await OpdVitalApi.update(vitals.id, payload)
      } else {
        await OpdVitalApi.create(payload)
      }
      notification.success({message: 'Vitals saved'})
      setVitalModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to save vitals',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const openDiagnosisModal = () => {
    diagnosisForm.resetFields()
    diagnosisForm.setFieldsValue({diagnosis_type: 'primary', is_primary: diagnoses.length === 0})
    setDiagnosisModalOpen(true)
  }

  const handleAddDiagnosis = async (values: any) => {
    setSubmitting(true)
    try {
      await OpdDiagnosisApi.create({
        opd_visit_id: itemData.id,
        patient_id: itemData.patient_id,
        diagnosis_name: values.diagnosis_name,
        icd10_code: values.icd10_code,
        icd10_description: values.icd10_description,
        diagnosis_type: values.diagnosis_type,
        is_primary: !!values.is_primary,
        is_chronic: !!values.is_chronic,
        notes: values.notes,
        sequence: diagnoses.length + 1,
      })
      notification.success({message: 'Diagnosis added'})
      setDiagnosisModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to add diagnosis',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const handleDeleteDiagnosis = async (id: number) => {
    try {
      await OpdDiagnosisApi.delete(id)
      notification.success({message: 'Diagnosis removed'})
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to remove diagnosis',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    }
  }

  const openPrescriptionModal = () => {
    prescriptionForm.setFieldsValue({
      advice: prescription?.advice,
      follow_up_date: prescription?.follow_up_date,
      items:
        prescriptionItems.length > 0
          ? prescriptionItems.map((it) => ({
              drug_name: it.drug_name,
              strength: it.strength,
              dose_value: it.dose_value,
              dose_unit: it.dose_unit,
              frequency: it.frequency,
              duration_value: it.duration_value,
              duration_unit: it.duration_unit,
              route: it.route,
              instruction: it.instruction,
            }))
          : [{frequency: 'OD', duration_unit: 'days', route: 'oral'}],
    })
    setPrescriptionModalOpen(true)
  }

  const handleSavePrescription = async (values: any) => {
    setSubmitting(true)
    try {
      await OpdPrescriptionApi.saveForVisit(itemData.id, {
        advice: values.advice,
        follow_up_date: values.follow_up_date,
        items: values.items || [],
      })
      notification.success({message: 'Prescription saved'})
      setPrescriptionModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to save prescription',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const handlePrintPrescription = async () => {
    if (!prescription?.id) return
    setPrintingPdf(true)
    try {
      const res: any = await OpdPrescriptionApi.pdf(prescription.id)
      const blob = new Blob([res.data], {type: 'application/pdf'})
      const url = window.URL.createObjectURL(blob)
      window.open(url, '_blank')
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to generate prescription PDF',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setPrintingPdf(false)
    }
  }

  const handleScheduleFollowUp = async (values: any) => {
    setSubmitting(true)
    try {
      const res: any = await OpdVisitApi.scheduleFollowUp(itemData.id, {
        follow_up_date: values.follow_up_date,
        follow_up_time: values.follow_up_time,
        doctor_id: values.doctor_id,
        notes: values.notes,
      })
      const appointmentNo = res?.data?.appointment_no
      notification.success({
        message: 'Follow-up scheduled',
        description: appointmentNo ? `Appointment ${appointmentNo} created.` : 'Appointment created.',
      })
      setFollowUpModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to schedule follow-up',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const handleGenerateBill = async () => {
    setGeneratingBill(true)
    try {
      await OpdBillApi.generate(itemData.id)
      notification.success({message: 'Bill generated'})
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to generate bill',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setGeneratingBill(false)
    }
  }

  const openPaymentModal = () => {
    paymentForm.resetFields()
    paymentForm.setFieldsValue({payments: [{payment_method: 'cash'}]})
    setPaymentModalOpen(true)
  }

  const handleRecordPayment = async (values: any) => {
    setSubmitting(true)
    try {
      await OpdBillApi.recordSplitPayment(bill.id, {payments: values.payments || []})
      notification.success({message: 'Payment recorded'})
      setPaymentModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to record payment',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const handlePrintReceipt = async () => {
    if (!bill?.id) return
    setPrintingReceipt(true)
    try {
      const res: any = await OpdBillApi.receiptPdf(bill.id)
      const blob = new Blob([res.data], {type: 'application/pdf'})
      const url = window.URL.createObjectURL(blob)
      window.open(url, '_blank')
    } catch (e: any) {
      notification.error({
        message: 'Failed to generate receipt',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setPrintingReceipt(false)
    }
  }

  const handleApplyDiscount = async (values: any) => {
    setSubmitting(true)
    try {
      await OpdBillApi.applyDiscount(bill.id, {
        amount: values.amount,
        type: values.type,
        reason: values.reason,
      })
      notification.success({message: 'Discount applied'})
      setDiscountModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to apply discount',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const handleApproveDiscount = async () => {
    setSubmitting(true)
    try {
      await OpdBillApi.approveDiscount(bill.id)
      notification.success({message: 'Discount approved'})
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to approve discount',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const handleRejectDiscount = async () => {
    setSubmitting(true)
    try {
      await OpdBillApi.rejectDiscount(bill.id, {reason: 'Rejected by supervisor'})
      notification.success({message: 'Discount rejected'})
      notifyReload()
    } catch (e: any) {
      notification.error({
        message: 'Failed to reject discount',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const diagnosisColumns = [
    {title: 'ICD-10', dataIndex: 'icd10_code', key: 'icd10_code', width: '15%', render: (v: string) => v || '-'},
    {title: 'Diagnosis', dataIndex: 'diagnosis_name', key: 'diagnosis_name'},
    {
      title: 'Type',
      dataIndex: 'diagnosis_type',
      key: 'diagnosis_type',
      width: '15%',
      render: (v: string, row: any) => (
        <Space>
          <Tag color={v === 'primary' ? 'blue' : 'default'} className='text-capitalize'>{v}</Tag>
          {row.is_primary && <Tag color='gold'>Primary</Tag>}
          {row.is_chronic && <Tag color='volcano'>Chronic</Tag>}
        </Space>
      ),
    },
    ...(isLocked
      ? []
      : [
          {
            title: 'Action',
            key: 'action',
            width: '10%',
            render: (_: any, row: any) => (
              <Popconfirm title='Remove this diagnosis?' onConfirm={() => handleDeleteDiagnosis(row.id)}>
                <Button size='small' danger icon={<DeleteOutlined />} />
              </Popconfirm>
            ),
          },
        ]),
  ]

  const prescriptionColumns = [
    {
      title: 'Drug',
      dataIndex: 'drug_name',
      key: 'drug_name',
      render: (v: string, row: any) => (
        <span>
          <strong>{v}</strong>
          {row.strength ? ` (${row.strength})` : ''}
        </span>
      ),
    },
    {title: 'Dose', dataIndex: 'dose_display', key: 'dose_display', render: (v: string) => v || '-'},
    {title: 'Frequency', dataIndex: 'frequency', key: 'frequency'},
    {title: 'Duration', dataIndex: 'duration_display', key: 'duration_display', render: (v: string) => v || '-'},
    {title: 'Route', dataIndex: 'route', key: 'route', render: (v: string) => <span className='text-capitalize'>{v}</span>},
    {title: 'Instruction', dataIndex: 'instruction', key: 'instruction', render: (v: string) => v || '-'},
  ]

  // ── TAB: CLINICAL (SOAP) ─────────────────────────────────────────────────
  const clinicalTab = (
    <div>
      <AllergyBanner patientId={itemData.patient_id} />
      <Descriptions title='S · Subjective' bordered column={1} size='small' className='mb-6'>
        <Descriptions.Item label='Chief Complaint'>{itemData.chief_complaint || '-'}</Descriptions.Item>
        <Descriptions.Item label='History'>
          <span style={{whiteSpace: 'pre-wrap'}}>{itemData.history || '-'}</span>
        </Descriptions.Item>
      </Descriptions>

      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>O · Objective — Vitals</h5>
        {!isLocked && (
          <Button size='small' icon={vitals ? <EditOutlined /> : <PlusOutlined />} onClick={openVitalModal}>
            {vitals ? 'Edit Vitals' : 'Record Vitals'}
          </Button>
        )}
      </div>
      {vitals ? (
        <Descriptions bordered column={3} size='small' className='mb-4'>
          <Descriptions.Item label='BP'>{vitals.bp_display || '-'}</Descriptions.Item>
          <Descriptions.Item label='Pulse'>{vitals.pulse ?? '-'} bpm</Descriptions.Item>
          <Descriptions.Item label='Temp'>{vitals.temperature ?? '-'} °C</Descriptions.Item>
          <Descriptions.Item label='SpO2'>{vitals.spo2 ?? '-'} %</Descriptions.Item>
          <Descriptions.Item label='Weight'>{vitals.weight ?? '-'} kg</Descriptions.Item>
          <Descriptions.Item label='Height'>{vitals.height ?? '-'} cm</Descriptions.Item>
          <Descriptions.Item label='BMI' span={3}>
            {vitals.bmi ?? '-'} {vitals.bmi_category ? `(${vitals.bmi_category})` : ''}
          </Descriptions.Item>
        </Descriptions>
      ) : (
        <Empty description='No vitals recorded yet' className='mb-4' />
      )}
      <Descriptions bordered column={1} size='small' className='mb-6'>
        <Descriptions.Item label='Examination Findings'>
          <span style={{whiteSpace: 'pre-wrap'}}>{itemData.examination || '-'}</span>
        </Descriptions.Item>
      </Descriptions>

      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>A · Assessment — Diagnoses</h5>
        {!isLocked && (
          <Button size='small' icon={<PlusOutlined />} onClick={openDiagnosisModal}>
            Add Diagnosis
          </Button>
        )}
      </div>
      {diagnoses.length > 0 ? (
        <Table
          rowKey='id'
          size='small'
          className='mb-4'
          columns={diagnosisColumns}
          dataSource={diagnoses}
          pagination={false}
        />
      ) : (
        <Empty description='No diagnoses recorded yet' className='mb-4' />
      )}
      <Descriptions bordered column={1} size='small' className='mb-6'>
        <Descriptions.Item label='Clinical Notes'>
          <span style={{whiteSpace: 'pre-wrap'}}>{itemData.clinical_notes || '-'}</span>
        </Descriptions.Item>
      </Descriptions>

      <Descriptions title='P · Plan' bordered column={1} size='small' className='mb-6'>
        <Descriptions.Item label='Advice'>
          <span style={{whiteSpace: 'pre-wrap'}}>{itemData.advice || '-'}</span>
        </Descriptions.Item>
      </Descriptions>

      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>e-Prescription</h5>
        <Space>
          {prescriptionItems.length > 0 && (
            <Button
              size='small'
              icon={<PrinterOutlined />}
              loading={printingPdf}
              onClick={handlePrintPrescription}
            >
              {prescription?.is_printed ? 'Re-print PDF' : 'Print PDF'}
            </Button>
          )}
          {!isLocked && (
            <Button
              size='small'
              icon={prescriptionItems.length > 0 ? <EditOutlined /> : <FileTextOutlined />}
              onClick={openPrescriptionModal}
            >
              {prescriptionItems.length > 0 ? 'Edit Prescription' : 'Write Prescription'}
            </Button>
          )}
        </Space>
      </div>
      {prescriptionItems.length > 0 ? (
        <Table
          rowKey='id'
          size='small'
          columns={prescriptionColumns}
          dataSource={prescriptionItems}
          pagination={false}
        />
      ) : (
        <Empty description='No prescription written yet' />
      )}
    </div>
  )

  // ── TAB: OVERVIEW ─────────────────────────────────────────────────────────
  const overviewTab = (
    <div>
      <Descriptions title='Visit Information' bordered column={2} size='small' className='mb-6'>
        <Descriptions.Item label='OPD No'>{itemData.opd_no}</Descriptions.Item>
        <Descriptions.Item label='Token'>{itemData.token_number || '-'}</Descriptions.Item>
        <Descriptions.Item label='Doctor'>{itemData.doctor_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='Department'>{itemData.department_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='Visit Date'>
          {itemData.visit_date ? DateTimeUtils.formatDate(itemData.visit_date) : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Visit Type'>
          <span className='text-capitalize'>{(itemData.visit_type || '-').replace('_', ' ')}</span>
        </Descriptions.Item>
        <Descriptions.Item label='Status'>
          <Badge color={statusColor(itemData.status)} className='text-capitalize'>
            {(itemData.status || 'unknown').replace('_', ' ')}
          </Badge>
        </Descriptions.Item>
        <Descriptions.Item label='Linked Appointment'>{itemData.appointment_no || '-'}</Descriptions.Item>
      </Descriptions>

      <Descriptions title='Patient Information' bordered column={2} size='small' className='mb-6'>
        <Descriptions.Item label='Patient Name'>{itemData.patient_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='MRN'>{itemData.patient_mrn || '-'}</Descriptions.Item>
        <Descriptions.Item label='Phone'>{itemData.patient_phone || '-'}</Descriptions.Item>
        <Descriptions.Item label='Gender'>
          <span className='text-capitalize'>{itemData.patient_gender || '-'}</span>
        </Descriptions.Item>
        <Descriptions.Item label='Date of Birth' span={2}>
          {itemData.patient_dob ? DateTimeUtils.formatDate(itemData.patient_dob) : '-'}
        </Descriptions.Item>
      </Descriptions>

      <Divider />
      <Descriptions column={2} size='small' className='text-muted fs-7'>
        <Descriptions.Item label='Created By'>{itemData.created_by_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='Created At'>
          {itemData.created_at ? DateTimeUtils.formatDateTime(itemData.created_at) : '-'}
        </Descriptions.Item>
        <Descriptions.Item label='Updated By'>{itemData.updated_by_name || '-'}</Descriptions.Item>
        <Descriptions.Item label='Updated At'>
          {itemData.updated_at ? DateTimeUtils.formatDateTime(itemData.updated_at) : '-'}
        </Descriptions.Item>
      </Descriptions>
    </div>
  )

  // ── TAB: BILLING ─────────────────────────────────────────────────────────
  const billItemColumns = [
    {title: 'Type', dataIndex: 'item_type_label', key: 'item_type_label', width: '18%'},
    {title: 'Description', dataIndex: 'description', key: 'description'},
    {title: 'Qty', dataIndex: 'quantity', key: 'quantity', width: '10%', align: 'right' as const},
    {title: 'Unit Price', dataIndex: 'unit_price', key: 'unit_price', width: '15%', align: 'right' as const},
    {title: 'Amount', dataIndex: 'amount', key: 'amount', width: '15%', align: 'right' as const},
  ]

  const billPaymentColumns = [
    {title: 'Method', dataIndex: 'payment_method_label', key: 'payment_method_label', width: '20%'},
    {title: 'Amount', dataIndex: 'amount', key: 'amount', width: '15%', align: 'right' as const},
    {title: 'Reference', dataIndex: 'reference_no', key: 'reference_no', render: (v: string) => v || '-'},
    {
      title: 'Paid At',
      dataIndex: 'paid_at',
      key: 'paid_at',
      width: '18%',
      render: (v: string) => (v ? DateTimeUtils.formatDateTimeA(v) : '-'),
    },
  ]

  const billingTab = (
    <div>
      {!bill ? (
        <div className='text-center py-10'>
          <Empty description='No bill generated for this visit yet' />
          <Button
            type='primary'
            className='mt-3'
            loading={generatingBill}
            onClick={handleGenerateBill}
          >
            Generate Bill
          </Button>
        </div>
      ) : (
        <>
          <div className='d-flex justify-content-between align-items-center mb-3'>
            <div>
              <h5 className='mb-0'>{bill.bill_no}</h5>
              <span className='text-muted fs-7'>
                {bill.billed_at ? DateTimeUtils.formatDateTimeA(bill.billed_at) : ''}
              </span>
            </div>
            <Space>
              <Tag color={bill.is_paid ? 'green' : bill.is_partial ? 'gold' : bill.is_waived ? 'default' : 'red'}>
                {bill.status_label}
              </Tag>
              {!bill.is_paid && !bill.is_waived && bill.discount_status !== 'pending_approval' && (
                <Button
                  size='small'
                  icon={<PercentageOutlined />}
                  onClick={() => {
                    discountForm.resetFields()
                    setDiscountModalOpen(true)
                  }}
                >
                  Apply Discount
                </Button>
              )}
              {bill.balance > 0 && (
                <Button size='small' type='primary' icon={<DollarOutlined />} onClick={openPaymentModal}>
                  Collect Payment
                </Button>
              )}
              <Button size='small' icon={<PrinterOutlined />} loading={printingReceipt} onClick={handlePrintReceipt}>
                Print Receipt
              </Button>
            </Space>
          </div>

          <Table
            rowKey='id'
            size='small'
            className='mb-4'
            columns={billItemColumns}
            dataSource={billItems}
            pagination={false}
          />

          <Row gutter={16} className='mb-4'>
            <Col span={12}>
              <Descriptions bordered size='small' column={1}>
                <Descriptions.Item label='Subtotal'>{bill.subtotal}</Descriptions.Item>
                <Descriptions.Item label='Discount'>{bill.discount}</Descriptions.Item>
                <Descriptions.Item label='Tax'>{bill.tax}</Descriptions.Item>
                <Descriptions.Item label='Total'>{bill.total}</Descriptions.Item>
                <Descriptions.Item label='Paid'>{bill.paid}</Descriptions.Item>
                <Descriptions.Item label='Balance'>{bill.balance}</Descriptions.Item>
              </Descriptions>
            </Col>
            {bill.discount_status && bill.discount_status !== 'none' && (
              <Col span={12}>
                <Descriptions bordered size='small' column={1} title='Discount / Concession'>
                  <Descriptions.Item label='Status'>
                    <Tag
                      color={
                        bill.discount_status === 'approved'
                          ? 'green'
                          : bill.discount_status === 'pending_approval'
                          ? 'gold'
                          : bill.discount_status === 'rejected'
                          ? 'red'
                          : 'default'
                      }
                      className='text-capitalize'
                    >
                      {bill.discount_status.replace('_', ' ')}
                    </Tag>
                  </Descriptions.Item>
                  {bill.discount_status === 'pending_approval' && (
                    <Descriptions.Item label='Pending Amount'>{bill.pending_discount}</Descriptions.Item>
                  )}
                  <Descriptions.Item label='Reason'>{bill.discount_reason || '-'}</Descriptions.Item>
                  {bill.discount_status === 'pending_approval' && hasPermission('auth:opd-bill:approveDiscount') && (
                    <Descriptions.Item label='Action'>
                      <Space>
                        <Button
                          size='small'
                          type='primary'
                          icon={<CheckOutlined />}
                          loading={submitting}
                          onClick={handleApproveDiscount}
                        >
                          Approve
                        </Button>
                        <Popconfirm title='Reject this discount request?' onConfirm={handleRejectDiscount}>
                          <Button size='small' danger icon={<CloseOutlined />} loading={submitting}>
                            Reject
                          </Button>
                        </Popconfirm>
                      </Space>
                    </Descriptions.Item>
                  )}
                </Descriptions>
              </Col>
            )}
          </Row>

          <h5>Payments</h5>
          {billPayments.length > 0 ? (
            <Table
              rowKey='id'
              size='small'
              columns={billPaymentColumns}
              dataSource={billPayments}
              pagination={false}
            />
          ) : (
            <Empty description='No payments recorded yet' />
          )}
        </>
      )}
    </div>
  )

  const tabItems = [
    {key: 'overview', label: 'Overview', children: overviewTab},
    {key: 'clinical', label: 'Clinical (SOAP)', children: clinicalTab},
    {key: 'billing', label: 'Billing', children: billingTab},
    {
      key: 'audit',
      label: 'Audit Log',
      children: <AuditLogPanel fetchFn={() => OpdVisitApi.auditLog(itemData.id)} reloadKey={itemData.id} />,
    },
  ]

  return (
    <div className='view-page-content p-6'>
      {/* ============= HEADER ============= */}
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <div>
          <h3 className='mb-1'>{itemData.opd_no}</h3>
          <div className='text-muted fs-7'>
            {itemData.visit_date ? DateTimeUtils.formatDate(itemData.visit_date) : ''} · Token{' '}
            {itemData.token_number || '-'}
          </div>
        </div>
        <Space>
          {itemData.status === 'completed' && (
            <Button size='small' icon={<CalendarOutlined />} onClick={() => setFollowUpModalOpen(true)}>
              Schedule Follow-up
            </Button>
          )}
          <Badge color={statusColor(itemData.status)} className='text-capitalize fs-6'>
            {(itemData.status || 'unknown').replace('_', ' ')}
          </Badge>
        </Space>
      </div>

      <Tabs items={tabItems} type='card' size='small' />

      {/* ============= VITALS MODAL ============= */}
      <Modal
        title={vitals ? 'Edit Vitals' : 'Record Vitals'}
        open={vitalModalOpen}
        onCancel={() => setVitalModalOpen(false)}
        onOk={() => vitalForm.submit()}
        confirmLoading={submitting}
      >
        <Form form={vitalForm} layout='vertical' onFinish={handleSaveVitals}>
          <Space wrap>
            <Form.Item name='systolic' label='Systolic (mmHg)'>
              <InputNumber min={50} max={300} />
            </Form.Item>
            <Form.Item name='diastolic' label='Diastolic (mmHg)'>
              <InputNumber min={30} max={200} />
            </Form.Item>
            <Form.Item name='pulse' label='Pulse (bpm)'>
              <InputNumber min={20} max={250} />
            </Form.Item>
          </Space>
          <Space wrap>
            <Form.Item name='temperature' label='Temp (°C)'>
              <InputNumber min={25} max={45} step={0.1} />
            </Form.Item>
            <Form.Item name='spo2' label='SpO2 (%)'>
              <InputNumber min={50} max={100} />
            </Form.Item>
          </Space>
          <Space wrap>
            <Form.Item name='weight' label='Weight (kg)'>
              <InputNumber min={0.5} max={500} step={0.1} />
            </Form.Item>
            <Form.Item name='height' label='Height (cm)'>
              <InputNumber min={20} max={260} step={0.1} />
            </Form.Item>
          </Space>
          <Form.Item name='notes' label='Notes'>
            <TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>

      {/* ============= DIAGNOSIS MODAL ============= */}
      <Modal
        title='Add Diagnosis'
        open={diagnosisModalOpen}
        onCancel={() => setDiagnosisModalOpen(false)}
        onOk={() => diagnosisForm.submit()}
        confirmLoading={submitting}
      >
        <Form form={diagnosisForm} layout='vertical' onFinish={handleAddDiagnosis}>
          <Form.Item
            name='diagnosis_name'
            label='Diagnosis'
            rules={[{required: true, message: 'Diagnosis name is required'}]}
          >
            <Input placeholder='e.g. Acute upper respiratory infection' />
          </Form.Item>
          <Space wrap>
            <Form.Item name='icd10_code' label='ICD-10 Code'>
              <Input placeholder='e.g. J06.9' style={{width: 160}} />
            </Form.Item>
            <Form.Item name='diagnosis_type' label='Type'>
              <Select style={{width: 160}}>
                <Option value='primary'>Primary</Option>
                <Option value='secondary'>Secondary</Option>
                <Option value='differential'>Differential</Option>
                <Option value='final'>Final</Option>
              </Select>
            </Form.Item>
          </Space>
          <Form.Item name='icd10_description' label='ICD-10 Description'>
            <Input placeholder='Optional' />
          </Form.Item>
          <Space>
            <Form.Item name='is_primary' label='Primary' valuePropName='checked'>
              <Switch />
            </Form.Item>
            <Form.Item name='is_chronic' label='Chronic' valuePropName='checked'>
              <Switch />
            </Form.Item>
          </Space>
          <Form.Item name='notes' label='Notes'>
            <TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>

      {/* ============= PRESCRIPTION MODAL ============= */}
      <Modal
        title={prescriptionItems.length > 0 ? 'Edit Prescription' : 'Write Prescription'}
        open={prescriptionModalOpen}
        onCancel={() => setPrescriptionModalOpen(false)}
        onOk={() => prescriptionForm.submit()}
        confirmLoading={submitting}
        width={800}
      >
        <Form form={prescriptionForm} layout='vertical' onFinish={handleSavePrescription}>
          <Form.List name='items'>
            {(fields, {add, remove}) => (
              <>
                {fields.map((field) => (
                  <div key={field.key} className='border rounded p-3 mb-3'>
                    <Row gutter={8}>
                      <Col span={7}>
                        <Form.Item
                          {...field}
                          name={[field.name, 'drug_name']}
                          label='Drug'
                          rules={[{required: true, message: 'Required'}]}
                        >
                          <Input placeholder='Drug name' />
                        </Form.Item>
                      </Col>
                      <Col span={4}>
                        <Form.Item {...field} name={[field.name, 'strength']} label='Strength'>
                          <Input placeholder='e.g. 500mg' />
                        </Form.Item>
                      </Col>
                      <Col span={3}>
                        <Form.Item {...field} name={[field.name, 'dose_value']} label='Dose'>
                          <InputNumber min={0} style={{width: '100%'}} />
                        </Form.Item>
                      </Col>
                      <Col span={3}>
                        <Form.Item {...field} name={[field.name, 'dose_unit']} label='Unit'>
                          <Input placeholder='tab' />
                        </Form.Item>
                      </Col>
                      <Col span={3}>
                        <Form.Item {...field} name={[field.name, 'frequency']} label='Freq'>
                          <Select>
                            {['OD', 'BD', 'TID', 'QID', 'HS', 'SOS', 'STAT', 'PRN'].map((f) => (
                              <Option key={f} value={f}>{f}</Option>
                            ))}
                          </Select>
                        </Form.Item>
                      </Col>
                      <Col span={3}>
                        <Form.Item {...field} name={[field.name, 'duration_value']} label='For'>
                          <InputNumber min={1} style={{width: '100%'}} />
                        </Form.Item>
                      </Col>
                      <Col span={1} className='d-flex align-items-center'>
                        {fields.length > 1 && (
                          <Button
                            danger
                            type='text'
                            icon={<DeleteOutlined />}
                            onClick={() => remove(field.name)}
                          />
                        )}
                      </Col>
                    </Row>
                    <Row gutter={8}>
                      <Col span={4}>
                        <Form.Item {...field} name={[field.name, 'duration_unit']} label='Duration Unit' initialValue='days'>
                          <Select>
                            <Option value='days'>Days</Option>
                            <Option value='weeks'>Weeks</Option>
                            <Option value='months'>Months</Option>
                          </Select>
                        </Form.Item>
                      </Col>
                      <Col span={4}>
                        <Form.Item {...field} name={[field.name, 'route']} label='Route' initialValue='oral'>
                          <Select>
                            {['oral', 'iv', 'im', 'sc', 'topical', 'inhalation', 'rectal', 'other'].map((r) => (
                              <Option key={r} value={r}>{r}</Option>
                            ))}
                          </Select>
                        </Form.Item>
                      </Col>
                      <Col span={16}>
                        <Form.Item {...field} name={[field.name, 'instruction']} label='Instruction'>
                          <Input placeholder='e.g. After meals' />
                        </Form.Item>
                      </Col>
                    </Row>
                  </div>
                ))}
                <Button
                  type='dashed'
                  block
                  icon={<PlusOutlined />}
                  onClick={() => add({frequency: 'OD', duration_unit: 'days', route: 'oral'})}
                  className='mb-3'
                >
                  Add Drug
                </Button>
              </>
            )}
          </Form.List>

          <Form.Item name='advice' label='Advice'>
            <TextArea rows={2} placeholder='General advice for this prescription' />
          </Form.Item>
          <Form.Item name='follow_up_date' label='Follow-up Date'>
            <Input type='date' style={{width: 200}} />
          </Form.Item>
        </Form>
      </Modal>

      {/* ============= FOLLOW-UP MODAL ============= */}
      <Modal
        title='Schedule Follow-up'
        open={followUpModalOpen}
        onCancel={() => setFollowUpModalOpen(false)}
        onOk={() => followUpForm.submit()}
        confirmLoading={submitting}
      >
        <Form form={followUpForm} layout='vertical' onFinish={handleScheduleFollowUp}>
          <Form.Item
            name='follow_up_date'
            label='Follow-up Date'
            rules={[{required: true, message: 'Please select a date'}]}
          >
            <Input type='date' />
          </Form.Item>
          <Form.Item name='follow_up_time' label='Time' initialValue='10:00'>
            <Input type='time' />
          </Form.Item>
          <Form.Item
            name='doctor_id'
            label='Doctor'
            tooltip='Defaults to the same doctor as this visit'
          >
            <InputNumber min={1} style={{width: '100%'}} placeholder={`Doctor ID (default: ${itemData.doctor_id})`} />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <TextArea rows={2} placeholder='Optional notes for the follow-up' />
          </Form.Item>
        </Form>
      </Modal>

      {/* ============= PAYMENT MODAL ============= */}
      <Modal
        title='Collect Payment'
        open={paymentModalOpen}
        onCancel={() => setPaymentModalOpen(false)}
        onOk={() => paymentForm.submit()}
        confirmLoading={submitting}
        width={640}
      >
        {bill && (
          <div className='alert alert-info mb-3'>
            Outstanding balance: <strong>{bill.balance}</strong>
          </div>
        )}
        <Form form={paymentForm} layout='vertical' onFinish={handleRecordPayment}>
          <Form.List name='payments'>
            {(fields, {add, remove}) => (
              <>
                {fields.map((field) => (
                  <Row gutter={8} key={field.key} align='middle'>
                    <Col span={7}>
                      <Form.Item
                        {...field}
                        name={[field.name, 'payment_method']}
                        label='Mode'
                        rules={[{required: true, message: 'Required'}]}
                      >
                        <Select>
                          <Option value='cash'>Cash</Option>
                          <Option value='card'>Card</Option>
                          <Option value='mobile'>Mobile Banking</Option>
                          <Option value='insurance'>Insurance</Option>
                          <Option value='other'>Other</Option>
                        </Select>
                      </Form.Item>
                    </Col>
                    <Col span={6}>
                      <Form.Item
                        {...field}
                        name={[field.name, 'amount']}
                        label='Amount'
                        rules={[{required: true, message: 'Required'}]}
                      >
                        <InputNumber min={0.01} style={{width: '100%'}} />
                      </Form.Item>
                    </Col>
                    <Col span={8}>
                      <Form.Item {...field} name={[field.name, 'reference_no']} label='Reference'>
                        <Input placeholder='Optional' />
                      </Form.Item>
                    </Col>
                    <Col span={3}>
                      {fields.length > 1 && (
                        <Button danger type='text' icon={<DeleteOutlined />} onClick={() => remove(field.name)} />
                      )}
                    </Col>
                  </Row>
                ))}
                <Button type='dashed' block icon={<PlusOutlined />} onClick={() => add({payment_method: 'cash'})}>
                  Add Payment Mode
                </Button>
              </>
            )}
          </Form.List>
        </Form>
      </Modal>

      {/* ============= DISCOUNT MODAL ============= */}
      <Modal
        title='Apply Discount'
        open={discountModalOpen}
        onCancel={() => setDiscountModalOpen(false)}
        onOk={() => discountForm.submit()}
        confirmLoading={submitting}
      >
        <Form form={discountForm} layout='vertical' onFinish={handleApplyDiscount} initialValues={{type: 'percent'}}>
          <Space wrap>
            <Form.Item
              name='amount'
              label='Amount'
              rules={[{required: true, message: 'Amount is required'}]}
            >
              <InputNumber min={0.01} style={{width: 160}} />
            </Form.Item>
            <Form.Item name='type' label='Type' rules={[{required: true}]}>
              <Select style={{width: 140}}>
                <Option value='percent'>Percent (%)</Option>
                <Option value='flat'>Flat Amount</Option>
              </Select>
            </Form.Item>
          </Space>
          <Form.Item name='reason' label='Reason'>
            <TextArea rows={2} placeholder='Why is this discount being given?' />
          </Form.Item>
          <div className='text-muted fs-7'>
            Discounts above 10% of the bill subtotal require supervisor approval before being applied.
          </div>
        </Form>
      </Modal>
    </div>
  )
}

export default React.memo(OpdVisitView)

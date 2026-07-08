import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Checkbox, Col, Form, Input, Row, Space, Spin, Table, Tag, Typography} from 'antd'
import {useNavigate, useParams} from 'react-router-dom'
import {AnaesthesiaRecordApi, OtBookingApi, SurgeryNoteApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title, Text} = Typography

const statusColor: Record<string, string> = {
  scheduled: 'processing',
  in_progress: 'warning',
  completed: 'success',
  cancelled: 'error',
}

const signInItems = [
  {key: 'identity_confirmed', label: 'Patient identity, site, procedure & consent confirmed'},
  {key: 'site_marked', label: 'Surgical site marked'},
  {key: 'anaesthesia_check_complete', label: 'Anaesthesia safety check completed'},
  {key: 'allergy_checked', label: 'Known allergy reviewed' },
  {key: 'airway_risk_assessed', label: 'Difficult airway / aspiration risk assessed'},
]
const timeOutItems = [
  {key: 'team_introduced', label: 'All team members introduced by name and role'},
  {key: 'procedure_confirmed', label: 'Surgeon, anaesthetist, nurse verbally confirm patient/site/procedure'},
  {key: 'antibiotic_given', label: 'Antibiotic prophylaxis given in last 60 min (if applicable)'},
  {key: 'imaging_displayed', label: 'Essential imaging displayed (if applicable)'},
]
const signOutItems = [
  {key: 'procedure_recorded', label: 'Name of procedure recorded'},
  {key: 'instrument_count_correct', label: 'Instrument, sponge & needle counts correct'},
  {key: 'specimen_labeled', label: 'Specimen labeled (if applicable)'},
  {key: 'equipment_issues_addressed', label: 'Equipment problems addressed'},
]

const ChecklistBlock: FC<{
  title: string
  items: {key: string; label: string}[]
  value: Record<string, boolean>
  completedAt?: string | null
  disabled: boolean
  onSubmit: (checklist: Record<string, boolean>) => void
  submitting: boolean
}> = ({title, items, value, completedAt, disabled, onSubmit, submitting}) => {
  const [checked, setChecked] = useState<Record<string, boolean>>(value || {})

  useEffect(() => setChecked(value || {}), [value])

  return (
    <Card size='small' className='mb-3' title={title} extra={completedAt ? <Tag color='success'>Completed {completedAt}</Tag> : null}>
      <Space direction='vertical'>
        {items.map((item) => (
          <Checkbox
            key={item.key}
            disabled={disabled || !!completedAt}
            checked={!!checked[item.key]}
            onChange={(e) => setChecked({...checked, [item.key]: e.target.checked})}
          >
            {item.label}
          </Checkbox>
        ))}
      </Space>
      {!completedAt && (
        <div className='mt-3'>
          <Button type='primary' size='small' disabled={disabled} loading={submitting} onClick={() => onSubmit(checked)}>
            Complete {title}
          </Button>
        </div>
      )}
    </Card>
  )
}

const OtBookingViewController: FC = () => {
  const {id} = useParams()
  const navigate = useNavigate()
  const [loading, setLoading] = useState(true)
  const [booking, setBooking] = useState<any>(null)
  const [note, setNote] = useState<any>(null)
  const [anaesthesia, setAnaesthesia] = useState<any>(null)
  const [actioning, setActioning] = useState<string | null>(null)
  const [entryForm] = Form.useForm()

  const loadAll = () => {
    if (!id) return
    setLoading(true)
    OtBookingApi.getById(id)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data
        setBooking(data)
        setNote(data?.surgery_note || null)
        setAnaesthesia(data?.anaesthesia_record || null)
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }

  useEffect(() => {
    loadAll()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id])

  const handleStart = () => {
    setActioning('start')
    OtBookingApi.start(id)
      .then(() => {
        Message.success('Surgery started.')
        loadAll()
      })
      .catch((err: any) => Message.error(typeof err?.data === 'string' ? err.data : 'Could not start surgery.'))
      .finally(() => setActioning(null))
  }

  const handleComplete = () => {
    setActioning('complete')
    OtBookingApi.complete(id)
      .then(() => {
        Message.success('Surgery completed.')
        loadAll()
      })
      .catch((err: any) => Message.error(typeof err?.data === 'string' ? err.data : 'Could not complete surgery.'))
      .finally(() => setActioning(null))
  }

  const handleChecklist = (phase: 'signIn' | 'timeOut' | 'signOut', checklist: Record<string, boolean>) => {
    setActioning(phase)
    const call =
      phase === 'signIn'
        ? SurgeryNoteApi.signIn(id, {checklist})
        : phase === 'timeOut'
        ? SurgeryNoteApi.timeOut(id, {checklist})
        : SurgeryNoteApi.signOut(id, {checklist})
    call
      .then((res: any) => {
        setNote(res?.data ?? res?.data?.data)
        Message.success('Checklist saved.')
      })
      .catch((err: any) => Message.error(typeof err?.data === 'string' ? err.data : 'Failed to save checklist.'))
      .finally(() => setActioning(null))
  }

  const handleSurgeonSign = () => {
    setActioning('surgeonSign')
    SurgeryNoteApi.surgeonSign(id)
      .then((res: any) => {
        setNote(res?.data ?? res?.data?.data)
        Message.success('Surgery note signed.')
      })
      .catch((err: any) => Message.error(typeof err?.data === 'string' ? err.data : 'Failed to sign note.'))
      .finally(() => setActioning(null))
  }

  const handleStartAnaesthesia = () => {
    setActioning('anaesthesia-start')
    AnaesthesiaRecordApi.create(id, {anaesthesia_type: 'general'})
      .then((res: any) => {
        setAnaesthesia(res?.data ?? res?.data?.data)
        Message.success('Anaesthesia record started.')
      })
      .catch(() => Message.error('Failed to start anaesthesia record.'))
      .finally(() => setActioning(null))
  }

  const handleAddEntry = async () => {
    try {
      const values = await entryForm.validateFields()
      setActioning('entry')
      const res: any = await AnaesthesiaRecordApi.addEntry(anaesthesia.id, values)
      Message.success('Entry recorded.')
      entryForm.resetFields()
      loadAll()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to add entry.')
    } finally {
      setActioning(null)
    }
  }

  if (loading) return <Spin className='p-6' />
  if (!booking) return <div className='p-6'>Booking not found.</div>

  const entryColumns = [
    {title: 'Time', dataIndex: 'recorded_at', key: 'recorded_at'},
    {title: 'HR', dataIndex: 'heart_rate', key: 'heart_rate', render: (v: any) => v ?? '-'},
    {title: 'BP', key: 'bp', render: (_: any, r: any) => (r.bp_systolic ? `${r.bp_systolic}/${r.bp_diastolic}` : '-')},
    {title: 'SpO2', dataIndex: 'spo2_pct', key: 'spo2_pct', render: (v: any) => v ?? '-'},
    {title: 'Agent', key: 'agent', render: (_: any, r: any) => (r.agent_name ? `${r.agent_name} (${r.agent_dose || ''})` : '-')},
  ]

  return (
    <div className='p-6'>
      <Button className='mb-3' onClick={() => navigate('/admin/ot/booking')}>
        Back to List
      </Button>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <div>
          <Title level={3} className='mb-0'>
            {booking.booking_no} — {booking.surgery_name}
          </Title>
          <Text type='secondary'>
            {booking.patient?.first_name} {booking.patient?.last_name} (MRN: {booking.patient?.mrn}) — {booking.theatre?.name}
          </Text>
        </div>
        <Space>
          <Tag color={statusColor[booking.booking_status] || 'default'}>{booking.booking_status_label || booking.booking_status}</Tag>
          {booking.booking_status === 'scheduled' && (
            <Button type='primary' loading={actioning === 'start'} onClick={handleStart}>
              Start Surgery
            </Button>
          )}
          {booking.booking_status === 'in_progress' && (
            <Button type='primary' loading={actioning === 'complete'} onClick={handleComplete}>
              Complete Surgery
            </Button>
          )}
        </Space>
      </div>

      <Row gutter={16}>
        <Col span={14}>
          <Title level={5}>WHO Surgical Safety Checklist</Title>
          <ChecklistBlock
            title='Sign In (before induction)'
            items={signInItems}
            value={note?.who_sign_in_checklist}
            completedAt={note?.who_sign_in_at}
            disabled={actioning === 'signIn'}
            submitting={actioning === 'signIn'}
            onSubmit={(c) => handleChecklist('signIn', c)}
          />
          <ChecklistBlock
            title='Time Out (before incision)'
            items={timeOutItems}
            value={note?.who_time_out_checklist}
            completedAt={note?.who_time_out_at}
            disabled={actioning === 'timeOut' || !note?.who_sign_in_at}
            submitting={actioning === 'timeOut'}
            onSubmit={(c) => handleChecklist('timeOut', c)}
          />
          <ChecklistBlock
            title='Sign Out (before leaving OT)'
            items={signOutItems}
            value={note?.who_sign_out_checklist}
            completedAt={note?.who_sign_out_at}
            disabled={actioning === 'signOut' || !note?.who_time_out_at}
            submitting={actioning === 'signOut'}
            onSubmit={(c) => handleChecklist('signOut', c)}
          />

          <Card size='small' title='Surgeon Sign-off'>
            {note?.surgeon_signed_at ? (
              <Tag color='success'>Signed {note.surgeon_signed_at}</Tag>
            ) : (
              <Button
                type='primary'
                size='small'
                disabled={!note?.who_sign_out_at}
                loading={actioning === 'surgeonSign'}
                onClick={handleSurgeonSign}
              >
                Sign Surgery Note
              </Button>
            )}
          </Card>
        </Col>

        <Col span={10}>
          <Title level={5}>Anaesthesia Record</Title>
          <Card size='small'>
            {!anaesthesia ? (
              <Button type='primary' size='small' loading={actioning === 'anaesthesia-start'} onClick={handleStartAnaesthesia}>
                Start Anaesthesia Record
              </Button>
            ) : (
              <>
                <p>
                  <strong>Type:</strong> {anaesthesia.anaesthesia_type} &nbsp;
                  <strong>ASA:</strong> {anaesthesia.asa_grade || '-'}
                </p>
                <Table rowKey='id' size='small' columns={entryColumns} dataSource={anaesthesia.entries || []} pagination={false} className='mb-3' />
                <Form form={entryForm} layout='inline'>
                  <Form.Item name='heart_rate'>
                    <Input placeholder='HR' style={{width: 70}} />
                  </Form.Item>
                  <Form.Item name='bp_systolic'>
                    <Input placeholder='Sys' style={{width: 60}} />
                  </Form.Item>
                  <Form.Item name='bp_diastolic'>
                    <Input placeholder='Dia' style={{width: 60}} />
                  </Form.Item>
                  <Form.Item name='spo2_pct'>
                    <Input placeholder='SpO2' style={{width: 70}} />
                  </Form.Item>
                  <Form.Item name='agent_name'>
                    <Input placeholder='Agent' style={{width: 100}} />
                  </Form.Item>
                  <Form.Item name='agent_dose'>
                    <Input placeholder='Dose' style={{width: 80}} />
                  </Form.Item>
                  <Form.Item>
                    <Button type='primary' size='small' loading={actioning === 'entry'} onClick={handleAddEntry}>
                      Add Entry
                    </Button>
                  </Form.Item>
                </Form>
              </>
            )}
          </Card>
        </Col>
      </Row>
    </div>
  )
}

export default OtBookingViewController

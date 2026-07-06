import React, {FC, useEffect, useState} from 'react'
import {
  Badge,
  Descriptions,
  Tag,
  Tabs,
  Button,
  Space,
  Modal,
  Form,
  InputNumber,
  Input,
  Select,
  Table,
  Empty,
  notification,
} from 'antd'
import {PlusOutlined, PlayCircleOutlined, LogoutOutlined, LinkOutlined} from '@ant-design/icons'
import {DateTimeUtils} from 'src/app/utils'
import {ErVisitApi, ErTriageApi} from 'src/app/api'

const {TextArea} = Input
const {Option} = Select

const statusColor = (status: string): string => {
  switch (status) {
    case 'waiting_triage':
      return 'red'
    case 'triaged':
      return 'gold'
    case 'in_treatment':
      return 'blue'
    case 'admitted':
      return 'purple'
    case 'discharged':
      return 'green'
    default:
      return 'default'
  }
}

const triageColor = (color: string): string => {
  const map: Record<string, string> = {red: 'red', orange: 'orange', yellow: 'gold', green: 'green', blue: 'blue'}
  return map[color] || 'default'
}

const ElapsedTimer: FC<{since: string; targetMinutes: number}> = ({since, targetMinutes}) => {
  const [elapsedMin, setElapsedMin] = useState(0)

  useEffect(() => {
    const compute = () => setElapsedMin(Math.floor((Date.now() - new Date(since).getTime()) / 60000))
    compute()
    const interval = setInterval(compute, 15000)
    return () => clearInterval(interval)
  }, [since])

  const overdue = elapsedMin > targetMinutes
  return (
    <Tag color={overdue ? 'red' : 'green'}>
      {elapsedMin}m elapsed / {targetMinutes}m target {overdue ? '(OVERDUE)' : ''}
    </Tag>
  )
}

const ErVisitView: FC<any> = ({itemData, handleCallbackFunc}) => {
  const [submitting, setSubmitting] = useState(false)
  const [triageModalOpen, setTriageModalOpen] = useState(false)
  const [disposeModalOpen, setDisposeModalOpen] = useState(false)
  const [linkModalOpen, setLinkModalOpen] = useState(false)
  const [triageForm] = Form.useForm()
  const [disposeForm] = Form.useForm()
  const [linkForm] = Form.useForm()

  if (!itemData || !itemData.id) {
    return <div className='p-6'>No ER visit selected.</div>
  }

  const isTerminal = ['admitted', 'discharged', 'lwbs', 'deceased'].includes(itemData.er_status)
  const currentTriage = itemData.current_triage

  const notifyReload = () => {
    handleCallbackFunc?.('singleAction', 'reloadView')
    handleCallbackFunc?.('singleAction', 'reloadListing')
  }

  const errMsg = (e: any) => e?.response?.data?.message || e?.message || 'Unknown error'

  const handleTriage = async (values: any) => {
    setSubmitting(true)
    try {
      await ErTriageApi.create({...values, er_visit_id: itemData.id})
      notification.success({message: 'Triage recorded'})
      setTriageModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to record triage', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const handleStartTreatment = async () => {
    setSubmitting(true)
    try {
      await ErVisitApi.startTreatment(itemData.id)
      notification.success({message: 'Treatment started'})
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to start treatment', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const handleDispose = async (values: any) => {
    setSubmitting(true)
    try {
      await ErVisitApi.dispose(itemData.id, values)
      notification.success({message: 'Disposition recorded'})
      setDisposeModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to record disposition', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const handleLinkAdmission = async (values: any) => {
    setSubmitting(true)
    try {
      await ErVisitApi.linkAdmission(itemData.id, values)
      notification.success({message: 'Linked to admission'})
      setLinkModalOpen(false)
      notifyReload()
    } catch (e: any) {
      notification.error({message: 'Failed to link admission', description: errMsg(e)})
    } finally {
      setSubmitting(false)
    }
  }

  const triageColumns = [
    {title: 'Level', dataIndex: 'triage_level_label', key: 'triage_level_label', render: (v: string, row: any) => <Tag color={triageColor(row.color_band)}>{v}</Tag>},
    {title: 'BP', dataIndex: 'bp_display', key: 'bp_display', render: (v: string) => v || '-'},
    {title: 'Pulse', dataIndex: 'pulse_bpm', key: 'pulse_bpm', render: (v: any) => v ?? '-'},
    {title: 'Temp', dataIndex: 'temperature_c', key: 'temperature_c', render: (v: any) => v ?? '-'},
    {title: 'SpO2', dataIndex: 'spo2_pct', key: 'spo2_pct', render: (v: any) => v ?? '-'},
    {title: 'Triaged At', dataIndex: 'triaged_at', key: 'triaged_at', render: (v: string) => DateTimeUtils.formatDateTimeA(v)},
  ]

  const overviewTab = (
    <div>
      <Descriptions title='ER Visit' bordered column={2} size='small' className='mb-6'>
        <Descriptions.Item label='Visit No'>{itemData.er_visit_no}</Descriptions.Item>
        <Descriptions.Item label='Arrival Mode'>
          <span className='text-capitalize'>{itemData.arrival_mode}</span>
        </Descriptions.Item>
        <Descriptions.Item label='Patient'>{itemData.patient_name}</Descriptions.Item>
        <Descriptions.Item label='MRN'>{itemData.mrn || '-'}</Descriptions.Item>
        <Descriptions.Item label='Arrival At'>{DateTimeUtils.formatDateTimeA(itemData.arrival_at)}</Descriptions.Item>
        <Descriptions.Item label='Status'>
          <Badge color={statusColor(itemData.er_status)} className='text-capitalize'>
            {itemData.er_status_label}
          </Badge>
        </Descriptions.Item>
        <Descriptions.Item label='Chief Complaint' span={2}>
          {itemData.chief_complaint}
        </Descriptions.Item>
        {itemData.disposition && (
          <Descriptions.Item label='Disposition' span={2}>
            <span className='text-capitalize'>{itemData.disposition}</span> at {DateTimeUtils.formatDateTimeA(itemData.disposed_at)}
          </Descriptions.Item>
        )}
        {itemData.linked_admission_id && (
          <Descriptions.Item label='Linked Admission ID' span={2}>
            {itemData.linked_admission_id}
          </Descriptions.Item>
        )}
      </Descriptions>

      {currentTriage && (
        <div className='mb-4'>
          <h5>Current Triage</h5>
          <Space>
            <Tag color={triageColor(currentTriage.color_band)}>{currentTriage.triage_level_label}</Tag>
            <ElapsedTimer since={currentTriage.triaged_at} targetMinutes={currentTriage.target_minutes} />
          </Space>
        </div>
      )}

      <h5>Triage History</h5>
      {(itemData.triages || []).length > 0 ? (
        <Table rowKey='id' size='small' columns={triageColumns} dataSource={itemData.triages} pagination={false} />
      ) : (
        <Empty description='No triage recorded yet' />
      )}
    </div>
  )

  const tabItems = [{key: 'overview', label: 'Overview', children: overviewTab}]

  return (
    <div className='view-page-content p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <div>
          <h3 className='mb-1'>{itemData.er_visit_no}</h3>
          <div className='text-muted fs-7'>{itemData.patient_name}</div>
        </div>
        <Space wrap>
          {!isTerminal && (
            <Button size='small' icon={<PlusOutlined />} onClick={() => {
              triageForm.resetFields()
              setTriageModalOpen(true)
            }}>
              Record Triage
            </Button>
          )}
          {itemData.er_status === 'triaged' && (
            <Button size='small' type='primary' icon={<PlayCircleOutlined />} onClick={handleStartTreatment}>
              Start Treatment
            </Button>
          )}
          {!isTerminal && (
            <Button size='small' icon={<LinkOutlined />} onClick={() => setLinkModalOpen(true)}>
              Link Admission
            </Button>
          )}
          {!isTerminal && (
            <Button size='small' danger icon={<LogoutOutlined />} onClick={() => setDisposeModalOpen(true)}>
              Disposition
            </Button>
          )}
          <Badge color={statusColor(itemData.er_status)} className='text-capitalize fs-6'>
            {itemData.er_status_label}
          </Badge>
        </Space>
      </div>

      <Tabs items={tabItems} type='card' size='small' />

      <Modal title='Record Triage' open={triageModalOpen} onCancel={() => setTriageModalOpen(false)} onOk={() => triageForm.submit()} confirmLoading={submitting}>
        <Form form={triageForm} layout='vertical' onFinish={handleTriage}>
          <Form.Item name='triage_level' label='Triage Level (1 = most acute, 5 = least acute)' rules={[{required: true}]}>
            <Select>
              <Option value={1}>1 — Resuscitation</Option>
              <Option value={2}>2 — Emergent</Option>
              <Option value={3}>3 — Urgent</Option>
              <Option value={4}>4 — Less Urgent</Option>
              <Option value={5}>5 — Non-Urgent</Option>
            </Select>
          </Form.Item>
          <Space wrap>
            <Form.Item name='bp_systolic' label='Systolic'>
              <InputNumber style={{width: 100}} />
            </Form.Item>
            <Form.Item name='bp_diastolic' label='Diastolic'>
              <InputNumber style={{width: 100}} />
            </Form.Item>
            <Form.Item name='pulse_bpm' label='Pulse'>
              <InputNumber style={{width: 100}} />
            </Form.Item>
            <Form.Item name='temperature_c' label='Temp (°C)'>
              <InputNumber step={0.1} style={{width: 100}} />
            </Form.Item>
            <Form.Item name='spo2_pct' label='SpO2 (%)'>
              <InputNumber style={{width: 100}} />
            </Form.Item>
          </Space>
          <Form.Item name='notes' label='Notes'>
            <TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>

      <Modal title='Record Disposition' open={disposeModalOpen} onCancel={() => setDisposeModalOpen(false)} onOk={() => disposeForm.submit()} confirmLoading={submitting}>
        <Form form={disposeForm} layout='vertical' onFinish={handleDispose}>
          <Form.Item name='disposition' label='Disposition' rules={[{required: true}]}>
            <Select>
              <Option value='discharged'>Discharged</Option>
              <Option value='lwbs'>Left Without Being Seen</Option>
              <Option value='deceased'>Deceased</Option>
            </Select>
          </Form.Item>
          <div className='text-muted fs-7'>To admit this patient, use "Link Admission" after creating an IPD admission for them.</div>
        </Form>
      </Modal>

      <Modal title='Link to IPD Admission' open={linkModalOpen} onCancel={() => setLinkModalOpen(false)} onOk={() => linkForm.submit()} confirmLoading={submitting}>
        <Form form={linkForm} layout='vertical' onFinish={handleLinkAdmission}>
          <Form.Item name='admission_id' label='Admission ID' rules={[{required: true, message: 'Admission ID is required'}]}>
            <InputNumber min={1} style={{width: '100%'}} placeholder='Enter the IPD admission ID' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default React.memo(ErVisitView)

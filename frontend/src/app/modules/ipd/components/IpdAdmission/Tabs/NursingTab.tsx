import React, {FC, useEffect, useRef, useState} from 'react'
import {Row, Col, Card, Form, InputNumber, Input, Select, Button, Table, Tag, Empty, notification, Space} from 'antd'
import {PlusOutlined} from '@ant-design/icons'
import Chart from 'react-apexcharts'
import {IpdVitalApi, IpdNursingAssessmentApi, IpdFluidBalanceApi} from 'src/app/api'
import {DateTimeUtils} from 'src/app/utils'

const {TextArea} = Input
const {Option} = Select

interface NursingTabProps {
  admissionId: number
}

const fallRiskTag = (level?: string) => {
  if (!level) return null
  const color = level === 'high' ? 'red' : level === 'medium' ? 'gold' : 'green'
  return <Tag color={color} className='text-capitalize'>{level} risk</Tag>
}

const NursingTab: FC<NursingTabProps> = ({admissionId}) => {
  const [assessmentForm] = Form.useForm()
  const [vitalForm] = Form.useForm()
  const [fluidForm] = Form.useForm()

  const [assessment, setAssessment] = useState<any>(null)
  const [vitals, setVitals] = useState<any[]>([])
  const [fluidSummary, setFluidSummary] = useState<any[]>([])
  const [savingAssessment, setSavingAssessment] = useState(false)
  const [submittingVital, setSubmittingVital] = useState(false)
  const [submittingFluid, setSubmittingFluid] = useState(false)
  const autosaveTimer = useRef<any>(null)
  const dirtyRef = useRef(false)

  const loadAssessment = () => {
    IpdNursingAssessmentApi.byAdmission(admissionId)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? null
        setAssessment(data)
        if (data) assessmentForm.setFieldsValue(data)
      })
      .catch(() => setAssessment(null))
  }

  const loadVitals = () => {
    IpdVitalApi.byAdmission(admissionId)
      .then((res: any) => setVitals(res?.data?.data ?? res?.data ?? []))
      .catch(() => setVitals([]))
  }

  const loadFluidSummary = () => {
    IpdFluidBalanceApi.summary(admissionId)
      .then((res: any) => setFluidSummary(res?.data?.data ?? res?.data ?? []))
      .catch(() => setFluidSummary([]))
  }

  useEffect(() => {
    loadAssessment()
    loadVitals()
    loadFluidSummary()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [admissionId])

  // Auto-save the nursing assessment every 30s while the tab is open and
  // dirty (per F-10-01's "auto-saved every 30 sec" acceptance criteria).
  useEffect(() => {
    autosaveTimer.current = setInterval(() => {
      if (dirtyRef.current) {
        handleSaveAssessment(assessmentForm.getFieldsValue(), true)
      }
    }, 30000)
    return () => clearInterval(autosaveTimer.current)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [admissionId])

  const handleSaveAssessment = async (values: any, silent = false) => {
    setSavingAssessment(true)
    try {
      const payload = {...values, admission_id: admissionId}
      const res: any = assessment
        ? await IpdNursingAssessmentApi.update(assessment.id, payload)
        : await IpdNursingAssessmentApi.create(payload)
      setAssessment(res?.data?.data ?? res?.data)
      dirtyRef.current = false
      if (!silent) notification.success({message: 'Nursing assessment saved'})
    } catch (e: any) {
      if (!silent) notification.error({message: 'Failed to save assessment', description: e?.response?.data?.message})
    } finally {
      setSavingAssessment(false)
    }
  }

  const handleAddVital = async (values: any) => {
    setSubmittingVital(true)
    try {
      await IpdVitalApi.create({...values, admission_id: admissionId})
      notification.success({message: 'Vitals recorded'})
      vitalForm.resetFields()
      loadVitals()
    } catch (e: any) {
      notification.error({message: 'Failed to record vitals', description: e?.response?.data?.message})
    } finally {
      setSubmittingVital(false)
    }
  }

  const handleAddFluid = async (values: any) => {
    setSubmittingFluid(true)
    try {
      await IpdFluidBalanceApi.create({...values, admission_id: admissionId})
      notification.success({message: 'Fluid entry recorded'})
      fluidForm.resetFields()
      loadFluidSummary()
    } catch (e: any) {
      notification.error({message: 'Failed to record fluid entry', description: e?.response?.data?.message})
    } finally {
      setSubmittingFluid(false)
    }
  }

  const chartSeries = [
    {name: 'Systolic', data: vitals.map((v) => ({x: v.recorded_at, y: v.bp_systolic}))},
    {name: 'Diastolic', data: vitals.map((v) => ({x: v.recorded_at, y: v.bp_diastolic}))},
    {name: 'Pulse', data: vitals.map((v) => ({x: v.recorded_at, y: v.pulse_bpm}))},
  ]
  const chartOptions: any = {
    chart: {type: 'line', toolbar: {show: false}, zoom: {enabled: false}},
    xaxis: {type: 'datetime'},
    stroke: {width: 2, curve: 'straight'},
    markers: {size: 4},
    legend: {position: 'top'},
    colors: ['#dc3545', '#f1416c', '#7239ea'],
    annotations: {
      yaxis: [
        {y: 90, y2: 140, borderColor: '#50cd89', fillColor: '#50cd89', opacity: 0.05, label: {text: 'Normal Systolic'}},
      ],
    },
  }

  const vitalColumns = [
    {title: 'Recorded At', dataIndex: 'recorded_at', key: 'recorded_at', render: (v: string) => DateTimeUtils.formatDateTimeA(v)},
    {
      title: 'BP',
      dataIndex: 'bp_display',
      key: 'bp_display',
      render: (v: string, row: any) => (
        <span className={row.abnormal_fields?.includes('bp_systolic') || row.abnormal_fields?.includes('bp_diastolic') ? 'text-danger fw-bold' : ''}>
          {v || '-'}
        </span>
      ),
    },
    {title: 'Pulse', dataIndex: 'pulse_bpm', key: 'pulse_bpm', render: (v: any) => v ?? '-'},
    {title: 'Temp (°C)', dataIndex: 'temperature_c', key: 'temperature_c', render: (v: any) => v ?? '-'},
    {title: 'SpO2 (%)', dataIndex: 'spo2_pct', key: 'spo2_pct', render: (v: any) => v ?? '-'},
    {title: 'Pain', dataIndex: 'pain_score', key: 'pain_score', render: (v: any) => v ?? '-'},
  ]

  const fluidColumns = [
    {title: 'Date', dataIndex: 'date', key: 'date'},
    {title: 'Intake (mL)', dataIndex: 'intake', key: 'intake'},
    {title: 'Output (mL)', dataIndex: 'output', key: 'output'},
    {
      title: 'Balance (mL)',
      dataIndex: 'balance',
      key: 'balance',
      render: (v: number) => <span className={v < 0 ? 'text-danger' : 'text-success'}>{v}</span>,
    },
  ]

  return (
    <div>
      <Card title='Nursing Assessment' size='small' className='mb-4' extra={
        <Button size='small' type='primary' loading={savingAssessment} onClick={() => handleSaveAssessment(assessmentForm.getFieldsValue())}>
          Save
        </Button>
      }>
        <Form form={assessmentForm} layout='vertical' onValuesChange={() => (dirtyRef.current = true)}>
          <Row gutter={16}>
            <Col md={12} xs={24}>
              <Form.Item name='mobility_status' label='Mobility Status'>
                <Select allowClear placeholder='Select mobility status'>
                  <Option value='independent'>Independent</Option>
                  <Option value='assisted'>Assisted</Option>
                  <Option value='bedbound'>Bedbound</Option>
                  <Option value='wheelchair'>Wheelchair</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col md={12} xs={24}>
              <Form.Item name='nutrition_risk' label='Nutrition Risk'>
                <Input placeholder='e.g. Low risk, normal appetite' />
              </Form.Item>
            </Col>
            <Col md={12} xs={24}>
              <Form.Item name='fall_risk_score' label='Fall Risk Score (Morse Scale, 0-125)'>
                <InputNumber min={0} max={125} style={{width: '100%'}} />
              </Form.Item>
              {fallRiskTag(assessment?.fall_risk_level)}
            </Col>
            <Col md={12} xs={24}>
              <Form.Item name='pressure_injury_risk_score' label='Pressure Injury Risk (Braden Scale, 0-23)'>
                <InputNumber min={0} max={23} style={{width: '100%'}} />
              </Form.Item>
              {fallRiskTag(assessment?.pressure_injury_risk_level)}
            </Col>
            <Col span={24}>
              <Form.Item name='general_appearance' label='General Appearance'>
                <TextArea rows={2} />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item name='skin_integrity_notes' label='Skin Integrity Notes'>
                <TextArea rows={2} />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item name='care_plan_notes' label='Care Plan Notes'>
                <TextArea rows={2} />
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </Card>

      <Card
        title='Vitals Trend'
        size='small'
        className='mb-4'
        extra={
          <Button
            size='small'
            icon={<PlusOutlined />}
            onClick={() => {
              vitalForm.resetFields()
              vitalForm.setFieldsValue({recorded_at: new Date().toISOString().slice(0, 16)})
            }}
          >
            Record Vitals
          </Button>
        }
      >
        <Form form={vitalForm} layout='vertical' onFinish={handleAddVital} className='mb-4'>
          <Row gutter={8}>
            <Col md={4} xs={12}>
              <Form.Item name='bp_systolic' label='Systolic'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col md={4} xs={12}>
              <Form.Item name='bp_diastolic' label='Diastolic'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col md={4} xs={12}>
              <Form.Item name='pulse_bpm' label='Pulse'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col md={4} xs={12}>
              <Form.Item name='temperature_c' label='Temp (°C)'>
                <InputNumber step={0.1} style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col md={4} xs={12}>
              <Form.Item name='spo2_pct' label='SpO2 (%)'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col md={4} xs={12}>
              <Form.Item name='pain_score' label='Pain (0-10)'>
                <InputNumber min={0} max={10} style={{width: '100%'}} />
              </Form.Item>
            </Col>
          </Row>
          <Space>
            <Button size='small' type='primary' htmlType='submit' loading={submittingVital}>
              Save Vitals
            </Button>
          </Space>
        </Form>

        {vitals.length > 0 ? (
          <>
            <Chart options={chartOptions} series={chartSeries} type='line' height={260} />
            <Table rowKey='id' size='small' className='mt-4' columns={vitalColumns} dataSource={[...vitals].reverse()} pagination={{pageSize: 5}} />
          </>
        ) : (
          <Empty description='No vitals recorded yet' />
        )}
      </Card>

      <Card
        title='Fluid Balance'
        size='small'
        extra={
          <Button size='small' icon={<PlusOutlined />} onClick={() => fluidForm.resetFields()}>
            Add Entry
          </Button>
        }
      >
        <Form form={fluidForm} layout='vertical' onFinish={handleAddFluid} className='mb-4' initialValues={{balance_type: 'intake', shift: 'morning'}}>
          <Row gutter={8}>
            <Col md={5} xs={12}>
              <Form.Item name='balance_type' label='Type' rules={[{required: true}]}>
                <Select>
                  <Option value='intake'>Intake</Option>
                  <Option value='output'>Output</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col md={6} xs={12}>
              <Form.Item name='category' label='Category' rules={[{required: true}]}>
                <Input placeholder='oral, iv, urine, drain...' />
              </Form.Item>
            </Col>
            <Col md={5} xs={12}>
              <Form.Item name='amount_ml' label='Amount (mL)' rules={[{required: true}]}>
                <InputNumber min={0.01} style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col md={5} xs={12}>
              <Form.Item name='shift' label='Shift'>
                <Select>
                  <Option value='morning'>Morning</Option>
                  <Option value='evening'>Evening</Option>
                  <Option value='night'>Night</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col md={3} xs={24} className='d-flex align-items-end'>
              <Button type='primary' htmlType='submit' loading={submittingFluid} block>
                Add
              </Button>
            </Col>
          </Row>
        </Form>

        {fluidSummary.length > 0 ? (
          <Table rowKey='date' size='small' columns={fluidColumns} dataSource={fluidSummary} pagination={false} />
        ) : (
          <Empty description='No fluid balance entries yet' />
        )}
      </Card>
    </div>
  )
}

export default NursingTab

import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Form, Input, InputNumber, Modal, Select, Table, Tabs, Tag, Typography} from 'antd'
import Chart from 'react-apexcharts'
import {AnalyzerInterfaceApi, LabQcApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography
const {Option} = Select

const QcLots: FC = () => {
  const [loading, setLoading] = useState(false)
  const [lots, setLots] = useState<any[]>([])
  const [activeLotId, setActiveLotId] = useState<any>(null)
  const [runs, setRuns] = useState<any[]>([])
  const [summary, setSummary] = useState<any>({total_runs: 0, out_of_control_count: 0})
  const [lotModalOpen, setLotModalOpen] = useState(false)
  const [runModalOpen, setRunModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [lotForm] = Form.useForm()
  const [runForm] = Form.useForm()

  const loadLots = () => {
    setLoading(true)
    LabQcApi.lots()
      .then((res: any) => {
        const list = res?.data?.data ?? res?.data ?? []
        setLots(list)
        if (list.length > 0 && !activeLotId) {
          setActiveLotId(list[0].id)
        }
      })
      .catch(() => setLots([]))
      .finally(() => setLoading(false))
  }

  const loadLeveyJennings = (lotId: any) => {
    if (!lotId) return
    LabQcApi.leveyJennings(lotId)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setRuns(data.runs || [])
        setSummary(data.summary || {total_runs: 0, out_of_control_count: 0})
      })
      .catch(() => {
        setRuns([])
      })
  }

  useEffect(() => {
    loadLots()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  useEffect(() => {
    loadLeveyJennings(activeLotId)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeLotId])

  const handleSaveLot = async () => {
    try {
      const values = await lotForm.validateFields()
      setSaving(true)
      await LabQcApi.createLot(values)
      Message.success('QC lot created.')
      setLotModalOpen(false)
      loadLots()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to create QC lot.')
    } finally {
      setSaving(false)
    }
  }

  const handleRecordRun = async () => {
    try {
      const values = await runForm.validateFields()
      setSaving(true)
      await LabQcApi.recordRun(activeLotId, values)
      Message.success('QC run recorded.')
      setRunModalOpen(false)
      loadLeveyJennings(activeLotId)
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to record QC run.')
    } finally {
      setSaving(false)
    }
  }

  const activeLot = lots.find((l: any) => l.id === activeLotId)

  const chartCategories = runs.map((r: any) => new Date(r.run_date).toLocaleDateString())
  const mean = activeLot ? Number(activeLot.target_mean) : 0
  const sd = activeLot ? Number(activeLot.target_sd) : 0
  const chartSeries = [
    {name: 'Measured Value', data: runs.map((r: any) => Number(r.measured_value))},
    {name: 'Mean', data: runs.map(() => mean)},
    {name: '+2SD', data: runs.map(() => mean + 2 * sd)},
    {name: '-2SD', data: runs.map(() => mean - 2 * sd)},
  ]
  const chartOptions: any = {
    chart: {toolbar: {show: false}},
    stroke: {width: [3, 1, 1, 1], dashArray: [0, 0, 4, 4]},
    colors: ['#009ef7', '#50cd89', '#f1416c', '#f1416c'],
    xaxis: {categories: chartCategories},
    markers: {size: [5, 0, 0, 0]},
  }

  return (
    <div>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={4} className='mb-0'>
          QC Lots
        </Title>
        <Button type='primary' onClick={() => { lotForm.resetFields(); setLotModalOpen(true) }}>
          Add QC Lot
        </Button>
      </div>

      <Card loading={loading} className='mb-4'>
        <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
          <thead>
            <tr>
              <th>Lot Number</th>
              <th>Level</th>
              <th>Parameter</th>
              <th>Target Mean</th>
              <th>Target SD</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {lots.length === 0 && (
              <tr>
                <td colSpan={6} align='center'>No QC lots found.</td>
              </tr>
            )}
            {lots.map((lot: any) => (
              <tr key={lot.id} style={{background: lot.id === activeLotId ? '#f0f7ff' : undefined}}>
                <td>{lot.lot_number}</td>
                <td>{lot.level}</td>
                <td>{lot.parameter?.parameter_name || '-'}</td>
                <td>{lot.target_mean}</td>
                <td>{lot.target_sd}</td>
                <td>
                  <Button size='small' onClick={() => setActiveLotId(lot.id)}>
                    View Chart
                  </Button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>

      {activeLot && (
        <Card
          title={`Levey-Jennings — ${activeLot.lot_number} (${activeLot.level})`}
          extra={
            <Button type='primary' size='small' onClick={() => { runForm.resetFields(); setRunModalOpen(true) }}>
              Record Run
            </Button>
          }
        >
          <div className='mb-3'>
            <Tag color='blue'>Total Runs: {summary.total_runs}</Tag>
            <Tag color={summary.out_of_control_count > 0 ? 'red' : 'green'}>
              Out of Control: {summary.out_of_control_count}
            </Tag>
          </div>
          {runs.length > 0 ? (
            <Chart options={chartOptions} series={chartSeries} type='line' height={320} />
          ) : (
            <div className='text-center text-muted py-10'>No runs recorded yet.</div>
          )}
          <Table
            className='mt-4'
            rowKey='id'
            size='small'
            pagination={false}
            dataSource={runs}
            columns={[
              {title: 'Run Date', dataIndex: 'run_date', key: 'run_date'},
              {title: 'Measured Value', dataIndex: 'measured_value', key: 'measured_value'},
              {title: 'Z-Score', dataIndex: 'z_score', key: 'z_score'},
              {
                title: 'Status',
                key: 'status',
                render: (_: any, row: any) =>
                  row.is_out_of_control ? <Tag color='red'>Out of Control</Tag> : <Tag color='green'>In Control</Tag>,
              },
            ]}
          />
        </Card>
      )}

      <Modal title='Add QC Lot' open={lotModalOpen} onCancel={() => setLotModalOpen(false)} onOk={handleSaveLot} confirmLoading={saving}>
        <Form form={lotForm} layout='vertical'>
          <Form.Item name='lab_test_parameter_id' label='Lab Test Parameter ID' rules={[{required: true}]}>
            <InputNumber className='w-100' min={1} placeholder='e.g. 1 (Haemoglobin)' />
          </Form.Item>
          <Form.Item name='lot_number' label='Lot Number' rules={[{required: true}]}>
            <Input placeholder='e.g. HB-LOT-001' />
          </Form.Item>
          <Form.Item name='level' label='Level'>
            <Select
              options={[
                {value: 'Level 1', label: 'Level 1'},
                {value: 'Level 2', label: 'Level 2'},
                {value: 'Level 3', label: 'Level 3'},
              ]}
            />
          </Form.Item>
          <Form.Item name='target_mean' label='Target Mean' rules={[{required: true}]}>
            <InputNumber className='w-100' step={0.01} />
          </Form.Item>
          <Form.Item name='target_sd' label='Target SD' rules={[{required: true}]}>
            <InputNumber className='w-100' step={0.01} min={0} />
          </Form.Item>
          <Form.Item name='expiry_date' label='Expiry Date'>
            <Input type='date' />
          </Form.Item>
        </Form>
      </Modal>

      <Modal title='Record QC Run' open={runModalOpen} onCancel={() => setRunModalOpen(false)} onOk={handleRecordRun} confirmLoading={saving}>
        <Form form={runForm} layout='vertical'>
          <Form.Item name='measured_value' label='Measured Value' rules={[{required: true}]}>
            <InputNumber className='w-100' step={0.01} />
          </Form.Item>
          <Form.Item name='remarks' label='Remarks'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

const AnalyzerMessages: FC = () => {
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    AnalyzerInterfaceApi.messages()
      .then((res: any) => setRows(res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const statusColor: Record<string, string> = {success: 'green', partial: 'orange', failed: 'red', pending: 'default'}

  const handleImport = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      const results = values.results_text
        .split('\n')
        .filter((l: string) => l.trim())
        .map((l: string) => {
          const [parameter_name, result_value] = l.split(',').map((s: string) => s.trim())
          return {parameter_name, result_value}
        })
      await AnalyzerInterfaceApi.import({
        analyzer_name: values.analyzer_name,
        barcode: values.barcode,
        results,
      })
      Message.success('Analyzer message imported.')
      setModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to import analyzer message.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={4} className='mb-0'>
          Analyzer Messages
        </Title>
        <Button
          type='primary'
          onClick={() => {
            form.resetFields()
            setModalOpen(true)
          }}
        >
          Simulate Analyzer Import
        </Button>
      </div>

      <Card loading={loading}>
        <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
          <thead>
            <tr>
              <th>Received At</th>
              <th>Analyzer</th>
              <th>Barcode</th>
              <th>Matched Results</th>
              <th>Status</th>
              <th>Error</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 && (
              <tr>
                <td colSpan={6} align='center'>No analyzer messages found.</td>
              </tr>
            )}
            {rows.map((row: any) => (
              <tr key={row.id}>
                <td>{row.received_at}</td>
                <td>{row.analyzer_name || '-'}</td>
                <td>{row.barcode || '-'}</td>
                <td>{row.matched_result_count}</td>
                <td>
                  <Tag color={statusColor[row.parse_status] || 'default'}>{row.parse_status}</Tag>
                </td>
                <td className='text-muted fs-8'>{row.error_message || '-'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>

      <Modal
        title='Simulate Analyzer Import'
        open={modalOpen}
        onCancel={() => setModalOpen(false)}
        onOk={handleImport}
        confirmLoading={saving}
        width={600}
      >
        <p className='text-muted'>
          This project has no real analyzer/ASTM-HL7 hardware link — this form simulates the structured payload an
          analyzer interface would push.
        </p>
        <Form form={form} layout='vertical'>
          <Form.Item name='analyzer_name' label='Analyzer Name'>
            <Input placeholder='e.g. Sysmex XN-1000' />
          </Form.Item>
          <Form.Item name='barcode' label='Sample Barcode' rules={[{required: true}]}>
            <Input placeholder='e.g. BC-00001' />
          </Form.Item>
          <Form.Item
            name='results_text'
            label='Results (one per line: parameter_name, value)'
            rules={[{required: true}]}
          >
            <Input.TextArea rows={5} placeholder={'Haemoglobin, 13.5\nWBC Count, 7.2'} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

const LabQcController: FC = () => {
  return (
    <div className='p-6'>
      <Title level={3}>Lab Quality Control & Machine Interfacing</Title>
      <Tabs
        items={[
          {key: 'qc', label: 'QC Lots & Levey-Jennings', children: <QcLots />},
          {key: 'analyzer', label: 'Analyzer Messages', children: <AnalyzerMessages />},
        ]}
      />
    </div>
  )
}

export default LabQcController

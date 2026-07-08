import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Col, Form, Modal, Row, Select, Statistic, Table, Tag, Typography} from 'antd'
import {BloodUnitApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography

const outcomeOptions = [
  {value: 'negative', label: 'Negative'},
  {value: 'positive', label: 'Positive'},
]

const InventoryController: FC = () => {
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [summary, setSummary] = useState<any[]>([])
  const [expiring, setExpiring] = useState<any[]>([])
  const [screenModalOpen, setScreenModalOpen] = useState(false)
  const [activeUnit, setActiveUnit] = useState<any>(null)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    BloodUnitApi.list()
      .then((res: any) => setRows(res?.data?.results ?? res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
    BloodUnitApi.inventorySummary()
      .then((res: any) => setSummary(res?.data ?? []))
      .catch(() => setSummary([]))
    BloodUnitApi.expiringSoon(14)
      .then((res: any) => setExpiring(res?.data ?? []))
      .catch(() => setExpiring([]))
  }

  useEffect(() => {
    loadData()
  }, [])

  const openScreen = (unit: any) => {
    setActiveUnit(unit)
    form.resetFields()
    setScreenModalOpen(true)
  }

  const handleScreen = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await BloodUnitApi.recordScreening(activeUnit.id, values)
      Message.success('Screening recorded.')
      setScreenModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to record screening.')
    } finally {
      setSaving(false)
    }
  }

  const statusColor: Record<string, string> = {
    quarantine: 'default',
    available: 'green',
    reserved: 'blue',
    issued: 'purple',
    discarded: 'red',
    expired: 'red',
  }

  return (
    <div className='p-6'>
      <Title level={4}>Blood Inventory</Title>

      <Row gutter={[16, 16]} className='mb-4'>
        {summary.map((s: any, i: number) => (
          <Col span={4} key={i}>
            <Card size='small'>
              <Statistic title={`${s.blood_group} · ${s.component_type}`} value={s.unit_count} />
            </Card>
          </Col>
        ))}
      </Row>

      {expiring.length > 0 && (
        <Card size='small' className='mb-4' title='Expiring Within 14 Days'>
          {expiring.map((u: any) => (
            <Tag color='orange' key={u.id}>
              {u.bag_no} ({u.blood_group}) — {u.expiry_date}
            </Tag>
          ))}
        </Card>
      )}

      <Card loading={loading}>
        <Table
          rowKey='id'
          size='small'
          dataSource={rows}
          columns={[
            {title: 'Bag No.', dataIndex: 'bag_no', key: 'bag_no'},
            {title: 'Component', dataIndex: 'component_type', key: 'component_type'},
            {title: 'Blood Group', dataIndex: 'blood_group', key: 'blood_group'},
            {title: 'Collection', dataIndex: 'collection_date', key: 'collection_date'},
            {title: 'Expiry', dataIndex: 'expiry_date', key: 'expiry_date'},
            {
              title: 'Screening',
              dataIndex: 'screening_status',
              key: 'screening_status',
              render: (v: string) => <Tag color={v === 'passed' ? 'green' : v === 'failed' ? 'red' : 'default'}>{v}</Tag>,
            },
            {
              title: 'Status',
              dataIndex: 'unit_status',
              key: 'unit_status',
              render: (v: string) => <Tag color={statusColor[v] || 'default'}>{v}</Tag>,
            },
            {
              title: 'Action',
              key: 'action',
              render: (_: any, row: any) =>
                row.screening_status === 'pending' ? (
                  <Button size='small' onClick={() => openScreen(row)}>
                    Record Screening
                  </Button>
                ) : null,
            },
          ]}
        />
      </Card>

      <Modal title='Record Screening' open={screenModalOpen} onCancel={() => setScreenModalOpen(false)} onOk={handleScreen} confirmLoading={saving}>
        <p className='text-muted'>Any positive result discards the unit automatically.</p>
        <Form form={form} layout='vertical'>
          <Form.Item name='hiv' label='HIV' rules={[{required: true}]}>
            <Select options={outcomeOptions} />
          </Form.Item>
          <Form.Item name='hbsag' label='HBsAg (Hepatitis B)' rules={[{required: true}]}>
            <Select options={outcomeOptions} />
          </Form.Item>
          <Form.Item name='hcv' label='HCV (Hepatitis C)' rules={[{required: true}]}>
            <Select options={outcomeOptions} />
          </Form.Item>
          <Form.Item name='vdrl' label='VDRL (Syphilis)' rules={[{required: true}]}>
            <Select options={outcomeOptions} />
          </Form.Item>
          <Form.Item name='malaria' label='Malaria' rules={[{required: true}]}>
            <Select options={outcomeOptions} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default InventoryController

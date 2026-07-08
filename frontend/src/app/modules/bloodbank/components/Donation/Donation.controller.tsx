import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Form, Input, InputNumber, Modal, Select, Table, Typography} from 'antd'
import {BloodDonationApi, BloodDonorApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography
const {Option} = Select

const DonationController: FC = () => {
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [donors, setDonors] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    BloodDonationApi.list()
      .then((res: any) => setRows(res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    BloodDonorApi.eligible()
      .then((res: any) => setDonors(res?.data?.results ?? res?.data ?? []))
      .catch(() => setDonors([]))
  }, [])

  const handleSave = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await BloodDonationApi.create(values)
      Message.success('Donation recorded — a whole-blood unit was created automatically.')
      setModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error(typeof err?.data === 'string' ? err.data : 'Failed to record donation.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={4} className='mb-0'>
          Donations
        </Title>
        <Button
          type='primary'
          onClick={() => {
            form.resetFields()
            setModalOpen(true)
          }}
        >
          Record Donation
        </Button>
      </div>

      <Card loading={loading}>
        <Table
          rowKey='id'
          size='small'
          dataSource={rows}
          columns={[
            {title: 'Donation No.', dataIndex: 'donation_no', key: 'donation_no'},
            {title: 'Donor', key: 'donor', render: (_: any, row: any) => row.donor?.name || '-'},
            {title: 'Date', dataIndex: 'donation_date', key: 'donation_date'},
            {title: 'Volume (ml)', dataIndex: 'volume_ml', key: 'volume_ml'},
            {title: 'Hemoglobin (g/dL)', dataIndex: 'hemoglobin_g_dl', key: 'hemoglobin_g_dl', render: (v: any) => v ?? '-'},
            {
              title: 'Units Created',
              key: 'units',
              render: (_: any, row: any) => (row.units || []).map((u: any) => u.bag_no).join(', ') || '-',
            },
          ]}
        />
      </Card>

      <Modal title='Record Donation' open={modalOpen} onCancel={() => setModalOpen(false)} onOk={handleSave} confirmLoading={saving}>
        <Form form={form} layout='vertical'>
          <Form.Item name='donor_id' label='Donor' rules={[{required: true}]}>
            <Select showSearch optionFilterProp='children' placeholder='Select an eligible (non-deferred) donor'>
              {donors.map((d: any) => (
                <Option key={d.id} value={d.id}>
                  {d.name} ({d.blood_group}) — {d.donor_no}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='donation_date' label='Donation Date'>
            <Input type='date' />
          </Form.Item>
          <Form.Item name='volume_ml' label='Volume (ml)'>
            <InputNumber className='w-100' min={1} defaultValue={450} />
          </Form.Item>
          <Form.Item name='hemoglobin_g_dl' label='Hemoglobin Screening (g/dL)'>
            <InputNumber className='w-100' step={0.1} />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default DonationController

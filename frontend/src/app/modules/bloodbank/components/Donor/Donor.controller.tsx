import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Form, Input, Modal, Select, Table, Tag, Typography} from 'antd'
import {BloodDonorApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography

const bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']

const DonorController: FC = () => {
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [deferModalOpen, setDeferModalOpen] = useState(false)
  const [activeDonor, setActiveDonor] = useState<any>(null)
  const [saving, setSaving] = useState(false)
  const [form] = Form.useForm()
  const [deferForm] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    BloodDonorApi.list()
      .then((res: any) => setRows(res?.data?.results ?? res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  const handleSave = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await BloodDonorApi.create(values)
      Message.success('Donor registered.')
      setModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to register donor.')
    } finally {
      setSaving(false)
    }
  }

  const openDefer = (donor: any) => {
    setActiveDonor(donor)
    deferForm.resetFields()
    setDeferModalOpen(true)
  }

  const handleDefer = async () => {
    try {
      const values = await deferForm.validateFields()
      setSaving(true)
      await BloodDonorApi.setDeferral(activeDonor.id, values)
      Message.success('Deferral updated.')
      setDeferModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to update deferral.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={4} className='mb-0'>
          Blood Donors
        </Title>
        <Button
          type='primary'
          onClick={() => {
            form.resetFields()
            setModalOpen(true)
          }}
        >
          Register Donor
        </Button>
      </div>

      <Card loading={loading}>
        <Table
          rowKey='id'
          size='small'
          dataSource={rows}
          columns={[
            {title: 'Donor No.', dataIndex: 'donor_no', key: 'donor_no'},
            {title: 'Name', dataIndex: 'name', key: 'name'},
            {title: 'Blood Group', dataIndex: 'blood_group', key: 'blood_group'},
            {title: 'Phone', dataIndex: 'phone', key: 'phone', render: (v: string) => v || '-'},
            {title: 'Total Donations', dataIndex: 'total_donations', key: 'total_donations'},
            {title: 'Last Donation', dataIndex: 'last_donation_date', key: 'last_donation_date', render: (v: string) => v || '-'},
            {
              title: 'Status',
              key: 'status',
              render: (_: any, row: any) =>
                row.is_deferred ? (
                  <Tag color='red'>Deferred until {row.deferral_until_date}</Tag>
                ) : (
                  <Tag color='green'>Eligible</Tag>
                ),
            },
            {
              title: 'Action',
              key: 'action',
              render: (_: any, row: any) => (
                <Button size='small' onClick={() => openDefer(row)}>
                  {row.is_deferred ? 'Update Deferral' : 'Defer'}
                </Button>
              ),
            },
          ]}
        />
      </Card>

      <Modal title='Register Donor' open={modalOpen} onCancel={() => setModalOpen(false)} onOk={handleSave} confirmLoading={saving}>
        <Form form={form} layout='vertical'>
          <Form.Item name='name' label='Name' rules={[{required: true}]}>
            <Input />
          </Form.Item>
          <Form.Item name='gender' label='Gender'>
            <Select
              options={[
                {value: 'male', label: 'Male'},
                {value: 'female', label: 'Female'},
                {value: 'other', label: 'Other'},
              ]}
            />
          </Form.Item>
          <Form.Item name='dob' label='Date of Birth'>
            <Input type='date' />
          </Form.Item>
          <Form.Item name='blood_group' label='Blood Group' rules={[{required: true}]}>
            <Select options={bloodGroups.map((g) => ({value: g, label: g}))} />
          </Form.Item>
          <Form.Item name='phone' label='Phone'>
            <Input />
          </Form.Item>
          <Form.Item name='address' label='Address'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>

      <Modal title='Update Deferral' open={deferModalOpen} onCancel={() => setDeferModalOpen(false)} onOk={handleDefer} confirmLoading={saving}>
        <p className='text-muted'>Leave reason blank to clear the deferral.</p>
        <Form form={deferForm} layout='vertical'>
          <Form.Item name='reason' label='Deferral Reason'>
            <Input.TextArea rows={2} />
          </Form.Item>
          <Form.Item name='until_date' label='Deferred Until'>
            <Input type='date' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default DonorController

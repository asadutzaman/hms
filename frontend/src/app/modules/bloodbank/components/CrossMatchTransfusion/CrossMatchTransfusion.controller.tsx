import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Form, Input, Modal, Select, Table, Tag, Typography} from 'antd'
import {BloodCrossMatchApi, BloodTransfusionApi, BloodUnitApi, PatientApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography
const {Option} = Select

const CrossMatchTransfusionController: FC = () => {
  const [patientSearch, setPatientSearch] = useState('')
  const [patientOptions, setPatientOptions] = useState<any[]>([])
  const [activePatientId, setActivePatientId] = useState<any>(null)
  const [crossMatches, setCrossMatches] = useState<any[]>([])
  const [transfusions, setTransfusions] = useState<any[]>([])
  const [availableUnits, setAvailableUnits] = useState<any[]>([])
  const [cmModalOpen, setCmModalOpen] = useState(false)
  const [txModalOpen, setTxModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [cmForm] = Form.useForm()
  const [txForm] = Form.useForm()

  useEffect(() => {
    if (!patientSearch || patientSearch.length < 2) {
      setPatientOptions([])
      return
    }
    PatientApi.getByWhere({$search: patientSearch, $top: 10})
      .then((res: any) => setPatientOptions(res?.data?.results || []))
      .catch(() => setPatientOptions([]))
  }, [patientSearch])

  const loadPatientData = (patientId: any) => {
    if (!patientId) return
    BloodCrossMatchApi.byPatient(patientId)
      .then((res: any) => setCrossMatches(res?.data ?? []))
      .catch(() => setCrossMatches([]))
    BloodTransfusionApi.byPatient(patientId)
      .then((res: any) => setTransfusions(res?.data ?? []))
      .catch(() => setTransfusions([]))
  }

  useEffect(() => {
    loadPatientData(activePatientId)
  }, [activePatientId])

  const openCrossMatch = () => {
    if (!activePatientId) return Message.error('Select a patient first.')
    cmForm.resetFields()
    setCmModalOpen(true)
    BloodUnitApi.inventory()
      .then((res: any) => setAvailableUnits(res?.data ?? []))
      .catch(() => setAvailableUnits([]))
  }

  const handleCrossMatch = async () => {
    try {
      const values = await cmForm.validateFields()
      setSaving(true)
      await BloodCrossMatchApi.create({...values, patient_id: activePatientId})
      Message.success('Cross-match recorded.')
      setCmModalOpen(false)
      loadPatientData(activePatientId)
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error(typeof err?.data === 'string' ? err.data : 'Cross-match failed.')
    } finally {
      setSaving(false)
    }
  }

  const openTransfusion = (crossMatch: any) => {
    txForm.resetFields()
    txForm.setFieldsValue({cross_match_id: crossMatch.id, blood_unit_id: crossMatch.blood_unit_id})
    setTxModalOpen(true)
  }

  const handleTransfusion = async () => {
    try {
      const values = await txForm.validateFields()
      setSaving(true)
      await BloodTransfusionApi.create({...values, patient_id: activePatientId})
      Message.success('Transfusion recorded.')
      setTxModalOpen(false)
      loadPatientData(activePatientId)
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error(typeof err?.data === 'string' ? err.data : 'Failed to record transfusion.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className='p-6'>
      <Title level={4}>Cross Match & Transfusion</Title>

      <Card className='mb-4'>
        <label className='form-label'>Patient</label>
        <Select
          className='w-100'
          showSearch
          filterOption={false}
          placeholder='Search patient by name/phone/MRN'
          onSearch={setPatientSearch}
          onChange={setActivePatientId}
          notFoundContent={null}
        >
          {patientOptions.map((p: any) => (
            <Option key={p.id} value={p.id}>
              {p.full_name || `${p.first_name} ${p.last_name}`} ({p.mrn})
            </Option>
          ))}
        </Select>
      </Card>

      {activePatientId && (
        <>
          <Card
            className='mb-4'
            title='Cross Matches'
            extra={
              <Button type='primary' size='small' onClick={openCrossMatch}>
                New Cross Match
              </Button>
            }
          >
            <Table
              rowKey='id'
              size='small'
              dataSource={crossMatches}
              pagination={false}
              columns={[
                {title: 'Unit', key: 'unit', render: (_: any, r: any) => r.blood_unit?.bag_no || '-'},
                {title: 'Unit Group', key: 'unit_group', render: (_: any, r: any) => r.blood_unit?.blood_group || '-'},
                {title: 'Patient Group', dataIndex: 'patient_blood_group', key: 'patient_blood_group'},
                {
                  title: 'Result',
                  dataIndex: 'cross_match_result',
                  key: 'cross_match_result',
                  render: (v: string) => <Tag color={v === 'compatible' ? 'green' : 'red'}>{v}</Tag>,
                },
                {title: 'Performed At', dataIndex: 'performed_at', key: 'performed_at'},
                {
                  title: 'Action',
                  key: 'action',
                  render: (_: any, r: any) =>
                    r.cross_match_result === 'compatible' && !r.blood_unit?.transfusion ? (
                      <Button size='small' onClick={() => openTransfusion(r)}>
                        Transfuse
                      </Button>
                    ) : null,
                },
              ]}
            />
          </Card>

          <Card title='Transfusions'>
            <Table
              rowKey='id'
              size='small'
              dataSource={transfusions}
              pagination={false}
              columns={[
                {title: 'Unit', key: 'unit', render: (_: any, r: any) => r.blood_unit?.bag_no || '-'},
                {title: 'Started At', dataIndex: 'started_at', key: 'started_at'},
                {title: 'Ended At', dataIndex: 'ended_at', key: 'ended_at', render: (v: any) => v || '-'},
                {
                  title: 'Reaction',
                  dataIndex: 'reaction_observed',
                  key: 'reaction_observed',
                  render: (v: boolean) => (v ? <Tag color='red'>Observed</Tag> : <Tag color='green'>None</Tag>),
                },
              ]}
            />
          </Card>
        </>
      )}

      <Modal title='New Cross Match' open={cmModalOpen} onCancel={() => setCmModalOpen(false)} onOk={handleCrossMatch} confirmLoading={saving}>
        <Form form={cmForm} layout='vertical'>
          <Form.Item name='blood_unit_id' label='Blood Unit' rules={[{required: true}]}>
            <Select showSearch optionFilterProp='children'>
              {availableUnits.map((u: any) => (
                <Option key={u.id} value={u.id}>
                  {u.bag_no} — {u.blood_group} ({u.component_type})
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='patient_blood_group' label="Patient's Blood Group (optional override)">
            <Select allowClear options={['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'].map((g) => ({value: g, label: g}))} />
          </Form.Item>
          <Form.Item name='method' label='Method'>
            <Input placeholder='e.g. saline, coombs' />
          </Form.Item>
          <Form.Item name='cross_match_result' label='Result' rules={[{required: true}]}>
            <Select
              options={[
                {value: 'compatible', label: 'Compatible'},
                {value: 'incompatible', label: 'Incompatible'},
              ]}
            />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>

      <Modal title='Record Transfusion' open={txModalOpen} onCancel={() => setTxModalOpen(false)} onOk={handleTransfusion} confirmLoading={saving}>
        <Form form={txForm} layout='vertical'>
          <Form.Item name='blood_unit_id' hidden>
            <Input />
          </Form.Item>
          <Form.Item name='cross_match_id' hidden>
            <Input />
          </Form.Item>
          <Form.Item name='started_at' label='Start Time'>
            <Input type='datetime-local' />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default CrossMatchTransfusionController

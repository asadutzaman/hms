import React, {FC, useState} from 'react'
import {
  Button,
  DatePicker,
  Empty,
  Form,
  Input,
  Modal,
  Popconfirm,
  Select,
  Table,
  Tag,
} from 'antd'
import {DeleteOutlined, PlusOutlined} from '@ant-design/icons'
import {ReferralApi, OpdProcedureApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useDepartmentList} from 'src/app/hooks/lists/useDepartmentList'
import {useEmployeeList} from 'src/app/hooks/lists/useEmployeeList'

const {TextArea} = Input
const {Option} = Select

const urgencyColor: Record<string, string> = {
  routine: 'default',
  urgent: 'gold',
  emergency: 'red',
}

const referralStatusColor: Record<string, string> = {
  pending: 'default',
  accepted: 'blue',
  completed: 'green',
  cancelled: 'red',
}

interface ReferralProcedurePanelProps {
  opdVisitId: any
  patientId: any
  doctorId: any
  referrals: any[]
  procedures: any[]
  isLocked?: boolean
  onChanged?: () => void
}

// Combined "Referrals & Procedures" tab for OpdVisitView — both entities are
// small, visit-scoped clinical records with the same add/list/remove shape,
// so they share one panel rather than doubling up near-identical UI.
// Data comes down as props (eager-loaded on the OpdVisit resource, same
// convention as diagnoses/prescription) rather than a separate fetch.
const ReferralProcedurePanel: FC<ReferralProcedurePanelProps> = ({
  opdVisitId,
  patientId,
  doctorId,
  referrals,
  procedures,
  isLocked,
  onChanged,
}) => {
  const [referralModalOpen, setReferralModalOpen] = useState(false)
  const [procedureModalOpen, setProcedureModalOpen] = useState(false)
  const [referralForm] = Form.useForm()
  const [procedureForm] = Form.useForm()
  const {departmentList} = useDepartmentList()
  const {employeeList} = useEmployeeList()

  const openReferralModal = () => {
    referralForm.resetFields()
    referralForm.setFieldsValue({urgency: 'routine'})
    setReferralModalOpen(true)
  }

  const handleAddReferral = (values: any) => {
    ReferralApi.create({
      ...values,
      opd_visit_id: opdVisitId,
      patient_id: patientId,
      referring_doctor_id: doctorId,
    })
      .then(() => {
        Message.success('Referral created')
        setReferralModalOpen(false)
        onChanged?.()
      })
      .catch(() => Message.error('Failed to create referral'))
  }

  const handleDeleteReferral = (id: number) => {
    ReferralApi.delete(id)
      .then(() => {
        Message.success('Referral removed')
        onChanged?.()
      })
      .catch(() => Message.error('Failed to remove referral'))
  }

  const openProcedureModal = () => {
    procedureForm.resetFields()
    procedureForm.setFieldsValue({performed_by: doctorId})
    setProcedureModalOpen(true)
  }

  const handleAddProcedure = (values: any) => {
    OpdProcedureApi.create({
      ...values,
      opd_visit_id: opdVisitId,
      patient_id: patientId,
      performed_at: values.performed_at ? values.performed_at.toISOString() : new Date().toISOString(),
    })
      .then(() => {
        Message.success('Procedure recorded')
        setProcedureModalOpen(false)
        onChanged?.()
      })
      .catch(() => Message.error('Failed to record procedure'))
  }

  const handleDeleteProcedure = (id: number) => {
    OpdProcedureApi.delete(id)
      .then(() => {
        Message.success('Procedure removed')
        onChanged?.()
      })
      .catch(() => Message.error('Failed to remove procedure'))
  }

  const referralColumns = [
    {
      title: 'To',
      key: 'to',
      render: (_: any, row: any) =>
        row.external_facility_name ||
        row.referred_to_doctor_name ||
        row.referred_to_department_name ||
        '-',
    },
    {title: 'Reason', dataIndex: 'reason'},
    {
      title: 'Urgency',
      dataIndex: 'urgency',
      render: (v: string) => <Tag color={urgencyColor[v]}>{v}</Tag>,
    },
    {
      title: 'Status',
      dataIndex: 'referral_status',
      render: (v: string) => <Tag color={referralStatusColor[v]}>{v}</Tag>,
    },
    ...(isLocked
      ? []
      : [
          {
            title: 'Action',
            key: 'action',
            render: (_: any, row: any) => (
              <Popconfirm title='Remove this referral?' onConfirm={() => handleDeleteReferral(row.id)}>
                <Button size='small' danger icon={<DeleteOutlined />} />
              </Popconfirm>
            ),
          },
        ]),
  ]

  const procedureColumns = [
    {title: 'Procedure', dataIndex: 'procedure_name'},
    {title: 'Performed By', dataIndex: 'performed_by_name'},
    {title: 'Outcome', dataIndex: 'outcome', render: (v: string) => v || '-'},
    ...(isLocked
      ? []
      : [
          {
            title: 'Action',
            key: 'action',
            render: (_: any, row: any) => (
              <Popconfirm title='Remove this procedure?' onConfirm={() => handleDeleteProcedure(row.id)}>
                <Button size='small' danger icon={<DeleteOutlined />} />
              </Popconfirm>
            ),
          },
        ]),
  ]

  return (
    <div>
      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>Referrals</h5>
        {!isLocked && (
          <Button size='small' icon={<PlusOutlined />} onClick={openReferralModal}>
            Add Referral
          </Button>
        )}
      </div>
      {referrals.length > 0 ? (
        <Table rowKey='id' size='small' className='mb-6' columns={referralColumns} dataSource={referrals} pagination={false} />
      ) : (
        <Empty description='No referrals recorded' className='mb-6' />
      )}

      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>Procedures</h5>
        {!isLocked && (
          <Button size='small' icon={<PlusOutlined />} onClick={openProcedureModal}>
            Record Procedure
          </Button>
        )}
      </div>
      {procedures.length > 0 ? (
        <Table rowKey='id' size='small' columns={procedureColumns} dataSource={procedures} pagination={false} />
      ) : (
        <Empty description='No procedures recorded' />
      )}

      {/* ============= REFERRAL MODAL ============= */}
      <Modal
        title='Add Referral'
        open={referralModalOpen}
        onCancel={() => setReferralModalOpen(false)}
        onOk={() => referralForm.submit()}
      >
        <Form form={referralForm} layout='vertical' onFinish={handleAddReferral}>
          <Form.Item name='referred_to_department_id' label='Refer to Department'>
            <Select allowClear placeholder='Internal department'>
              {departmentList.map((d: any) => (
                <Option key={d.id} value={d.id}>{d.name}</Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='referred_to_doctor_id' label='Refer to Doctor'>
            <Select allowClear showSearch optionFilterProp='children' placeholder='Internal doctor'>
              {employeeList.map((e: any) => (
                <Option key={e.id} value={e.id}>{e.name_en}</Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='external_facility_name' label='Or External Facility'>
            <Input placeholder='e.g. City General Hospital' />
          </Form.Item>
          <Form.Item name='urgency' label='Urgency' rules={[{required: true}]}>
            <Select>
              <Option value='routine'>Routine</Option>
              <Option value='urgent'>Urgent</Option>
              <Option value='emergency'>Emergency</Option>
            </Select>
          </Form.Item>
          <Form.Item name='reason' label='Reason' rules={[{required: true, message: 'Required'}]}>
            <TextArea rows={3} />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>

      {/* ============= PROCEDURE MODAL ============= */}
      <Modal
        title='Record Procedure'
        open={procedureModalOpen}
        onCancel={() => setProcedureModalOpen(false)}
        onOk={() => procedureForm.submit()}
      >
        <Form form={procedureForm} layout='vertical' onFinish={handleAddProcedure}>
          <Form.Item name='procedure_name' label='Procedure Name' rules={[{required: true, message: 'Required'}]}>
            <Input placeholder='e.g. Wound Dressing' />
          </Form.Item>
          <Form.Item name='procedure_code' label='Procedure Code'>
            <Input placeholder='Optional' />
          </Form.Item>
          <Form.Item name='performed_by' label='Performed By' rules={[{required: true}]}>
            <Select showSearch optionFilterProp='children'>
              {employeeList.map((e: any) => (
                <Option key={e.id} value={e.id}>{e.name_en}</Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='performed_at' label='Performed At'>
            <DatePicker showTime style={{width: '100%'}} />
          </Form.Item>
          <Form.Item name='outcome' label='Outcome'>
            <Input placeholder='e.g. Completed without complications' />
          </Form.Item>
          <Form.Item name='notes' label='Notes'>
            <TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default ReferralProcedurePanel

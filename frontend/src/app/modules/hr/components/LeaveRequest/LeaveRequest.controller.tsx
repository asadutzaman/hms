import React, {FC, useEffect, useState} from 'react'
import {Button, Card, DatePicker, Form, Input, Modal, Select, Tag, Typography} from 'antd'
import dayjs from 'dayjs'
import {LeaveRequestApi, LeaveTypeApi, WorkflowApi, WorkflowTransitionApi} from 'src/app/api'
import {useEmployeeList} from 'src/app/hooks/lists/useEmployeeList'
import {Message} from 'src/app/utils'

const {Title} = Typography
const {Option} = Select

const statusColor: Record<string, string> = {
  DRAFT: 'default',
  SUBMITTED: 'processing',
  APPROVED: 'success',
  REJECTED: 'error',
  BACKWARD_INITIATION: 'warning',
}

const LeaveRequestController: FC = () => {
  const {employeeList} = useEmployeeList()
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [leaveTypes, setLeaveTypes] = useState<any[]>([])
  const [applyModalOpen, setApplyModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [actioningId, setActioningId] = useState<any>(null)
  const [form] = Form.useForm()

  const loadData = () => {
    setLoading(true)
    LeaveRequestApi.list()
      .then((res: any) => setRows(res?.data?.results ?? res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    LeaveTypeApi.list()
      .then((res: any) => setLeaveTypes(res?.data?.results ?? res?.data ?? []))
      .catch(() => setLeaveTypes([]))
  }, [])

  const handleApply = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await LeaveRequestApi.apply({
        ...values,
        start_date: dayjs(values.start_date).format('YYYY-MM-DD'),
        end_date: dayjs(values.end_date).format('YYYY-MM-DD'),
      })
      Message.success('Leave request submitted.')
      setApplyModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error(typeof err?.data === 'string' ? err.data : 'Failed to submit leave request.')
    } finally {
      setSaving(false)
    }
  }

  // Direct workflow-engine call — see project_hms_sprint9_scope memory on
  // why this bypasses the generic WorkflowActionController component (no
  // approvers are actually assigned in workflow_step_approvers in this
  // environment, which would hide the generic component's action buttons
  // entirely; this always shows Approve/Reject for any SUBMITTED request).
  const handleWorkflowAction = async (row: any, actionCode: 'APPROVE' | 'REJECT') => {
    setActioningId(row.id)
    try {
      const wfRes: any = await WorkflowApi.getByWhere({
        $filter: "type='LeaveRequest' AND workflow_code='LEAVE_REQUEST_APPROVAL'",
      })
      const workflow = wfRes?.data?.data ?? wfRes?.data
      const approvalStep = (workflow?.workflow_steps || []).find((s: any) => s.step_code === 'APPROVAL')
      if (!workflow?.id || !approvalStep?.id) {
        Message.error('Leave approval workflow is not configured.')
        return
      }

      await WorkflowTransitionApi.create({
        workflow_id: workflow.id,
        workflow_step_id: approvalStep.id,
        workflow_record_id: row.id,
        workflow_action_name: actionCode === 'APPROVE' ? 'Approve' : 'Reject',
        workflow_action_code: actionCode,
      })
      Message.success(actionCode === 'APPROVE' ? 'Leave approved.' : 'Leave rejected.')
      loadData()
    } catch (err: any) {
      Message.error(typeof err?.data === 'string' ? err.data : 'Action failed.')
    } finally {
      setActioningId(null)
    }
  }

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <Title level={3} className='mb-0'>
          Leave Requests
        </Title>
        <Button
          type='primary'
          onClick={() => {
            form.resetFields()
            setApplyModalOpen(true)
          }}
        >
          Apply for Leave
        </Button>
      </div>

      <Card loading={loading}>
        <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
          <thead>
            <tr>
              <th>Request No.</th>
              <th>Employee</th>
              <th>Leave Type</th>
              <th>Start</th>
              <th>End</th>
              <th>Days</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {rows.length === 0 && (
              <tr>
                <td colSpan={8} align='center'>
                  No leave requests found.
                </td>
              </tr>
            )}
            {rows.map((row: any) => (
              <tr key={row.id}>
                <td>{row.request_no}</td>
                <td>{row.employee?.name_en || '-'}</td>
                <td>{row.leave_type?.name || '-'}</td>
                <td>{row.start_date}</td>
                <td>{row.end_date}</td>
                <td>{row.total_days}</td>
                <td>
                  <Tag color={statusColor[row.process_status] || 'default'}>{row.process_status}</Tag>
                </td>
                <td>
                  {row.process_status === 'SUBMITTED' && (
                    <>
                      <Button
                        size='small'
                        type='primary'
                        loading={actioningId === row.id}
                        onClick={() => handleWorkflowAction(row, 'APPROVE')}
                        className='me-2'
                      >
                        Approve
                      </Button>
                      <Button size='small' danger loading={actioningId === row.id} onClick={() => handleWorkflowAction(row, 'REJECT')}>
                        Reject
                      </Button>
                    </>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </Card>

      <Modal title='Apply for Leave' open={applyModalOpen} onCancel={() => setApplyModalOpen(false)} onOk={handleApply} confirmLoading={saving}>
        <Form form={form} layout='vertical'>
          <Form.Item name='employee_id' label='Employee' rules={[{required: true}]}>
            <Select showSearch optionFilterProp='children'>
              {employeeList.map((emp: any) => (
                <Option key={emp.id} value={emp.id}>
                  {emp.name_en}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='leave_type_id' label='Leave Type' rules={[{required: true}]}>
            <Select>
              {leaveTypes.map((lt: any) => (
                <Option key={lt.id} value={lt.id}>
                  {lt.name}
                </Option>
              ))}
            </Select>
          </Form.Item>
          <Form.Item name='start_date' label='Start Date' rules={[{required: true}]}>
            <DatePicker className='w-100' />
          </Form.Item>
          <Form.Item name='end_date' label='End Date' rules={[{required: true}]}>
            <DatePicker className='w-100' />
          </Form.Item>
          <Form.Item name='reason' label='Reason'>
            <Input.TextArea rows={3} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default LeaveRequestController

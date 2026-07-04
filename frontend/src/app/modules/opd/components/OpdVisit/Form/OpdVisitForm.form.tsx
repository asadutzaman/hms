import React, {FC, useEffect, useState} from 'react'
import {Form, Input, Select, InputNumber, DatePicker, Tabs, Row, Col, AutoComplete, Alert} from 'antd'
import {PatientApi, DepartmentApi} from 'src/app/api'

const {TextArea} = Input
const {Option} = Select
const {TabPane} = Tabs

interface PatientOption {
  id: number
  full_name: string
  primary_phone: string
  mrn: number
}

const OpdVisitAddOrEditForm: FC<any> = ({formRef, isNewRecord, itemData}) => {
  const [patientOptions, setPatientOptions] = useState<PatientOption[]>([])
  const [patientSearch, setPatientSearch] = useState<string>('')
  const [departments, setDepartments] = useState<any[]>([])

  const watchedPatientId = Form.useWatch('patient_id', formRef)

  // Once a visit reaches a terminal status (closed/cancelled), clinical fields
  // become read-only — mirrors OpdVisitStatusEnum::isTerminal() in the backend.
  const isLocked = !isNewRecord && !!itemData?.is_terminal

  useEffect(() => {
    DepartmentApi.dropdown({status: 1})
      .then((res: any) => setDepartments(res?.data?.results || []))
      .catch(() => setDepartments([]))
  }, [])

  useEffect(() => {
    if (!patientSearch || patientSearch.length < 2) {
      setPatientOptions([])
      return
    }
    PatientApi.getByWhere({$search: patientSearch, $top: 10})
      .then((res: any) => setPatientOptions(res?.data?.results || []))
      .catch(() => setPatientOptions([]))
  }, [patientSearch])

  return (
    <div className='form-page-content'>
      {isLocked && (
        <Alert
          type='warning'
          showIcon
          className='mb-4'
          message={`This visit is ${itemData?.status?.replace('_', ' ')} — clinical notes are read-only.`}
        />
      )}

      <Tabs defaultActiveKey='visit'>
        {/* ============= VISIT TAB ============= */}
        <TabPane tab='Patient & Visit' key='visit'>
          <Row gutter={[16, 16]}>
            <Col md={12} xs={24}>
              <Form.Item
                name='patient_id'
                label='Patient'
                rules={[{required: true, message: 'Please select a patient'}]}
              >
                <AutoComplete
                  disabled={!isNewRecord}
                  placeholder='Search by name, phone or MRN'
                  onSearch={setPatientSearch}
                  onSelect={(_value: any, option: any) => {
                    formRef.setFieldsValue({patient_id: option.id})
                  }}
                  allowClear
                >
                  {patientOptions.map((p) => (
                    <AutoComplete.Option key={p.id} value={p.id}>
                      <div>
                        <strong>{p.full_name}</strong>{' '}
                        <span className='text-muted'>(MRN {p.mrn})</span>
                      </div>
                      <div className='text-muted fs-7'>{p.primary_phone}</div>
                    </AutoComplete.Option>
                  ))}
                </AutoComplete>
              </Form.Item>
              {watchedPatientId && (
                <div className='text-muted fs-7 mb-3'>
                  Selected patient ID: <strong>{watchedPatientId}</strong>
                </div>
              )}
            </Col>

            <Col md={6} xs={24}>
              <Form.Item
                name='department_id'
                label='Department'
                rules={[{required: true, message: 'Department is required'}]}
              >
                <Select
                  showSearch
                  placeholder='Select department'
                  optionFilterProp='children'
                  filterOption={(input, option: any) =>
                    option?.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
                  }
                >
                  {departments.map((d: any) => (
                    <Option key={d.id} value={d.id}>
                      {d.name}
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>

            <Col md={6} xs={24}>
              <Form.Item
                name='doctor_id'
                label='Doctor'
                rules={[{required: true, message: 'Doctor is required'}]}
              >
                <Input type='number' placeholder='Doctor ID (from employees table)' />
              </Form.Item>
            </Col>

            <Col md={6} xs={24}>
              <Form.Item
                name='visit_type'
                label='Visit Type'
                rules={[{required: true, message: 'Visit type is required'}]}
              >
                <Select placeholder='Select visit type'>
                  <Option value='walk_in'>Walk-in</Option>
                  <Option value='appointment'>Appointment</Option>
                  <Option value='follow_up'>Follow-up</Option>
                  <Option value='emergency'>Emergency</Option>
                </Select>
              </Form.Item>
            </Col>

            <Col md={6} xs={24}>
              <Form.Item
                name='visit_date'
                label='Visit Date'
                rules={[{required: true, message: 'Date is required'}]}
              >
                <DatePicker format='YYYY-MM-DD' style={{width: '100%'}} />
              </Form.Item>
            </Col>

            <Col md={6} xs={24}>
              <Form.Item name='token_number' label='Token Number' tooltip='Auto-assigned if left blank'>
                <InputNumber min={1} max={9999} style={{width: '100%'}} />
              </Form.Item>
            </Col>

            <Col md={6} xs={24}>
              <Form.Item
                name='appointment_id'
                label='Linked Appointment'
                tooltip='If this visit originated from a booked appointment'
              >
                <InputNumber min={1} style={{width: '100%'}} placeholder='Appointment ID' />
              </Form.Item>
            </Col>
          </Row>
        </TabPane>

        {/* ============= SUBJECTIVE ============= */}
        <TabPane tab='S · Subjective' key='subjective'>
          <Row gutter={[16, 16]}>
            <Col span={24}>
              <Form.Item name='chief_complaint' label='Chief Complaint'>
                <TextArea rows={2} disabled={isLocked} placeholder="Patient's own description of the problem" />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item name='history' label='History of Present Illness'>
                <TextArea rows={4} disabled={isLocked} placeholder='Onset, duration, associated symptoms, relevant past history' />
              </Form.Item>
            </Col>
          </Row>
        </TabPane>

        {/* ============= OBJECTIVE ============= */}
        <TabPane tab='O · Objective' key='objective'>
          <Row gutter={[16, 16]}>
            <Col span={24}>
              <Alert
                type='info'
                showIcon
                className='mb-3'
                message='Record vitals (BP, pulse, temperature, SpO2, weight, height) in the Vitals panel on the visit view page.'
              />
            </Col>
            <Col span={24}>
              <Form.Item name='examination' label='Examination Findings'>
                <TextArea rows={5} disabled={isLocked} placeholder='General and systemic examination findings' />
              </Form.Item>
            </Col>
          </Row>
        </TabPane>

        {/* ============= ASSESSMENT ============= */}
        <TabPane tab='A · Assessment' key='assessment'>
          <Row gutter={[16, 16]}>
            <Col span={24}>
              <Alert
                type='info'
                showIcon
                className='mb-3'
                message='Record structured diagnoses (with ICD-10 code) in the Diagnoses panel on the visit view page.'
              />
            </Col>
            <Col span={24}>
              <Form.Item name='clinical_notes' label='Clinical Notes / Assessment'>
                <TextArea rows={5} disabled={isLocked} placeholder='Clinical impression, differential considerations' />
              </Form.Item>
            </Col>
          </Row>
        </TabPane>

        {/* ============= PLAN ============= */}
        <TabPane tab='P · Plan' key='plan'>
          <Row gutter={[16, 16]}>
            <Col span={24}>
              <Form.Item name='advice' label='Advice / Plan'>
                <TextArea
                  rows={5}
                  disabled={isLocked}
                  placeholder='Treatment plan, investigations ordered, advice given, follow-up instructions'
                />
              </Form.Item>
            </Col>
          </Row>
        </TabPane>
      </Tabs>
    </div>
  )
}

export default React.memo(OpdVisitAddOrEditForm)

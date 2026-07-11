import React, {FC, useEffect, useState} from 'react'
import {
  Col,
  Row,
  Input,
  Select,
  InputNumber,
  Switch,
  DatePicker,
  TimePicker,
  Button,
  Form,
  Card,
  Badge,
  Popconfirm,
} from 'antd'
import {
  PlusOutlined,
  DeleteOutlined,
  ClockCircleOutlined,
  CopyOutlined,
} from '@ant-design/icons'
import dayjs, {Dayjs} from 'dayjs'
import {DATABASE_DATE_FORMAT} from 'src/app/utils/DateTimeUtils'
import DepartmentSelect from 'src/app/components/Dropdown/DepartmentSelect'
import {useDoctorList} from 'src/app/hooks/lists/useDoctorList'

const {TextArea} = Input
const {Option} = Select

// This form renders inside DrawerForm, which does NOT provide a <Form>
// wrapper — each form component owns its own <Form form={formRef}> and reads
// the CRUD props (initialValues/handleChange/handleSubmit/handleSubmitFailed).
// Weekly slots are kept in a hidden `slots` form field (single source of
// truth) rather than a separate state object.
const DoctorScheduleForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed, isEditMode} = props
  const {doctorList, loadingDoctorList} = useDoctorList()
  const slots: any[] = Form.useWatch('slots', formRef) || []

  const currentSlots = (): any[] => formRef.getFieldValue('slots') || []
  const setSlots = (updated: any[]) => formRef.setFieldsValue({slots: updated})

  const addSlot = () => {
    setSlots([
      ...currentSlots(),
      {
        id: null,
        day_of_week: 1,
        start_time: '09:00',
        end_time: '12:00',
        slot_duration_minutes: 15,
        max_patients_per_slot: 1,
        is_active: true,
      },
    ])
  }

  const removeSlot = (index: number) => {
    setSlots(currentSlots().filter((_, i) => i !== index))
  }

  const updateSlot = (index: number, key: string, value: any) => {
    const updated = [...currentSlots()]
    updated[index] = {...updated[index], [key]: value}
    setSlots(updated)
  }

  const cloneSlot = (index: number) => {
    const source = currentSlots()[index]
    setSlots([
      ...currentSlots(),
      {
        ...source,
        id: null,
        day_of_week: source.day_of_week < 6 ? source.day_of_week + 1 : 0,
      },
    ])
  }

  // Values follow Carbon's dayOfWeek (0=Sunday … 6=Saturday) to match the
  // backend validator (between:0,6) and materializeSlotsForDate.
  const dayOptions = [
    {value: 0, label: 'Sunday'},
    {value: 1, label: 'Monday'},
    {value: 2, label: 'Tuesday'},
    {value: 3, label: 'Wednesday'},
    {value: 4, label: 'Thursday'},
    {value: 5, label: 'Friday'},
    {value: 6, label: 'Saturday'},
  ]

  return (
    <div className='p-3'>
      <Form
        form={formRef}
        layout='vertical'
        name='doctorScheduleForm'
        scrollToFirstError
        initialValues={initialValues}
        onValuesChange={handleChange}
        onFinish={handleSubmit}
        onFinishFailed={handleSubmitFailed}
      >
        <Card title='Basic Information' className='mb-4'>
          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                label='Schedule Name'
                name='name'
                rules={[{required: true, message: 'Schedule name is required'}]}
              >
                <Input placeholder='e.g., Dr. Smith — Morning OPD' />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Code' name='code'>
                <Input placeholder='Auto-generated if blank' disabled={isEditMode} />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={12}>
              <Form.Item
                label='Doctor'
                name='doctor_id'
                rules={[{required: true, message: 'Doctor is required'}]}
              >
                <Select
                  showSearch
                  allowClear
                  loading={loadingDoctorList}
                  optionFilterProp='children'
                  placeholder='Select doctor'
                >
                  {doctorList.map((doc: any) => (
                    <Option key={`doctor-${doc.id}`} value={doc.id}>
                      {doc.name}
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Department' name='department_id'>
                <DepartmentSelect
                  departmentId={formRef.getFieldValue('department_id')}
                  placeholder='Select department'
                  allowClear
                  onSelect={(value: any) => formRef.setFieldsValue({department_id: value})}
                  onChange={(value: any) => formRef.setFieldsValue({department_id: value})}
                  onLoad={(value: any) => formRef.setFieldsValue({department_id: value})}
                />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={8}>
              <Form.Item label='Schedule Type' name='schedule_type' rules={[{required: true}]}>
                <Select>
                  <Option value='regular'>Regular</Option>
                  <Option value='temporary'>Temporary</Option>
                  <Option value='emergency'>Emergency</Option>
                  <Option value='on_call'>On Call</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label='Consultation Mode' name='consultation_mode'>
                <Select>
                  <Option value='in_person'>In Person</Option>
                  <Option value='telemedicine'>Telemedicine</Option>
                  <Option value='home_visit'>Home Visit</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label='Chamber ID' name='chamber_id'>
                <Input type='number' placeholder='Chamber ID (optional)' />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={6}>
              <Form.Item
                label='Slot Duration (min)'
                name='slot_duration_minutes'
                rules={[{required: true, message: 'Slot duration is required'}]}
              >
                <InputNumber min={5} max={240} step={5} style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col span={6}>
              <Form.Item
                label='Max Patients / Slot'
                name='max_patients_per_slot'
                rules={[{required: true, message: 'Max patients per slot is required'}]}
              >
                <InputNumber min={1} max={100} style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col span={6}>
              <Form.Item label='Buffer (min)' name='buffer_minutes'>
                <InputNumber min={0} max={120} step={5} style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col span={6}>
              <Form.Item label='Consultation Fee' name='consultation_fee'>
                <InputNumber min={0} style={{width: '100%'}} />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={16}>
            <Col span={8}>
              <Form.Item
                label='Effective From'
                name='effective_from'
                getValueProps={(v) => ({value: v ? dayjs(v) : null})}
                normalize={(v) => (v ? dayjs(v).format(DATABASE_DATE_FORMAT) : null)}
              >
                <DatePicker style={{width: '100%'}} format={DATABASE_DATE_FORMAT} />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item
                label='Effective To'
                name='effective_to'
                getValueProps={(v) => ({value: v ? dayjs(v) : null})}
                normalize={(v) => (v ? dayjs(v).format(DATABASE_DATE_FORMAT) : null)}
              >
                <DatePicker style={{width: '100%'}} format={DATABASE_DATE_FORMAT} />
              </Form.Item>
            </Col>
            <Col span={4}>
              <Form.Item label='Default' name='is_default' valuePropName='checked'>
                <Switch />
              </Form.Item>
            </Col>
            <Col span={4}>
              <Form.Item
                label='Active'
                name='status'
                getValueProps={(v) => ({checked: !!v})}
                normalize={(v) => (v ? 1 : 0)}
              >
                <Switch />
              </Form.Item>
            </Col>
          </Row>
        </Card>

        <Card
          title={
            <span>
              <ClockCircleOutlined /> Weekly Time Slots
            </span>
          }
          className='mb-4'
          extra={
            <Button type='dashed' icon={<PlusOutlined />} onClick={addSlot}>
              Add Slot
            </Button>
          }
        >
          <Form.Item name='slots' hidden>
            <Input />
          </Form.Item>

          {slots.length === 0 && (
            <div className='text-center text-muted py-4'>
              No slots yet. Click "Add Slot" to define when this doctor is available.
            </div>
          )}

          {slots.map((slot: any, index: number) => (
            <Row key={index} gutter={8} align='middle' className='mb-3 p-2 border rounded'>
              <Col span={3}>
                <Select
                  size='small'
                  value={slot.day_of_week}
                  onChange={(v) => updateSlot(index, 'day_of_week', v)}
                  style={{width: '100%'}}
                >
                  {dayOptions.map((d) => (
                    <Option key={d.value} value={d.value}>
                      {d.label.slice(0, 3)}
                    </Option>
                  ))}
                </Select>
              </Col>
              <Col span={4}>
                <TimePicker
                  size='small'
                  format='HH:mm'
                  value={slot.start_time ? dayjs(slot.start_time, 'HH:mm') : null}
                  onChange={(v: Dayjs | null) =>
                    updateSlot(index, 'start_time', v?.format('HH:mm') || '00:00')
                  }
                  style={{width: '100%'}}
                />
              </Col>
              <Col span={1} className='text-center'>
                →
              </Col>
              <Col span={4}>
                <TimePicker
                  size='small'
                  format='HH:mm'
                  value={slot.end_time ? dayjs(slot.end_time, 'HH:mm') : null}
                  onChange={(v: Dayjs | null) =>
                    updateSlot(index, 'end_time', v?.format('HH:mm') || '00:00')
                  }
                  style={{width: '100%'}}
                />
              </Col>
              <Col span={3}>
                <InputNumber
                  size='small'
                  min={5}
                  max={240}
                  step={5}
                  value={slot.slot_duration_minutes}
                  onChange={(v) => updateSlot(index, 'slot_duration_minutes', v)}
                  style={{width: '100%'}}
                  addonAfter='min'
                />
              </Col>
              <Col span={3}>
                <InputNumber
                  size='small'
                  min={1}
                  max={20}
                  value={slot.max_patients_per_slot}
                  onChange={(v) => updateSlot(index, 'max_patients_per_slot', v)}
                  style={{width: '100%'}}
                  addonAfter='pts'
                />
              </Col>
              <Col span={2} className='text-center'>
                <Badge
                  status={slot.is_active ? 'success' : 'default'}
                  text={
                    <Switch
                      size='small'
                      checked={!!slot.is_active}
                      onChange={(v) => updateSlot(index, 'is_active', v)}
                    />
                  }
                />
              </Col>
              <Col span={3} className='text-end'>
                <Button
                  size='small'
                  type='text'
                  icon={<CopyOutlined />}
                  onClick={() => cloneSlot(index)}
                  title='Clone to next day'
                />
                <Popconfirm
                  title='Remove this slot?'
                  onConfirm={() => removeSlot(index)}
                  okText='Yes'
                  cancelText='No'
                >
                  <Button size='small' type='text' danger icon={<DeleteOutlined />} />
                </Popconfirm>
              </Col>
            </Row>
          ))}
        </Card>

        <Card title='Exceptions / Blocked Dates'>
          <Form.Item
            label='Notes'
            name='notes'
            help='Use the Exceptions API to add blocked dates, holidays, or special hours'
          >
            <TextArea rows={3} placeholder='Internal notes about this schedule' />
          </Form.Item>
        </Card>
      </Form>
    </div>
  )
}

export default DoctorScheduleForm

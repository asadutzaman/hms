import React, {FC, useState} from 'react'
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
  Divider,
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
import {DepartmentApi} from 'src/app/api'
import {DateTimeUtils} from 'src/app/utils'
import {DATABASE_DATE_FORMAT} from 'src/app/utils/DateTimeUtils'

const {TextArea} = Input
const {Option} = Select

const DoctorScheduleForm: FC<any> = (props) => {
  const {form, fields, setFields, loading, isEditMode} = props
  const [slots, setSlots] = useState<any[]>(fields?.slots || [])
  const [departments, setDepartments] = useState<any[]>([])

  React.useEffect(() => {
    if (fields?.slots) setSlots(fields.slots)
  }, [fields?.slots])

  React.useEffect(() => {
    loadDepartments()
  }, [])

  const loadDepartments = async () => {
    try {
      const response: any = await DepartmentApi.dropdown()
      const data = response?.data?.data || response?.data || []
      setDepartments(Array.isArray(data) ? data : [])
    } catch (e) {
      console.error('Failed to load departments', e)
    }
  }

  const addSlot = () => {
    const newSlot = {
      id: null,
      day_of_week: 1,
      start_time: '09:00',
      end_time: '12:00',
      slot_duration_minutes: 15,
      max_patients: 1,
      is_active: true,
    }
    const updated = [...slots, newSlot]
    setSlots(updated)
    setFields({...fields, slots: updated})
    form.setFieldsValue({slots: updated})
  }

  const removeSlot = (index: number) => {
    const updated = slots.filter((_, i) => i !== index)
    setSlots(updated)
    setFields({...fields, slots: updated})
    form.setFieldsValue({slots: updated})
  }

  const updateSlot = (index: number, key: string, value: any) => {
    const updated = [...slots]
    updated[index] = {...updated[index], [key]: value}
    setSlots(updated)
    setFields({...fields, slots: updated})
    form.setFieldsValue({slots: updated})
  }

  const cloneSlot = (index: number) => {
    const source = slots[index]
    const clone = {
      ...source,
      id: null,
      day_of_week:
        source.day_of_week < 7 ? source.day_of_week + 1 : 1,
    }
    const updated = [...slots, clone]
    setSlots(updated)
    setFields({...fields, slots: updated})
    form.setFieldsValue({slots: updated})
  }

  const dayOptions = [
    {value: 1, label: 'Monday'},
    {value: 2, label: 'Tuesday'},
    {value: 3, label: 'Wednesday'},
    {value: 4, label: 'Thursday'},
    {value: 5, label: 'Friday'},
    {value: 6, label: 'Saturday'},
    {value: 7, label: 'Sunday'},
  ]

  return (
    <div className='p-3'>
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
              <Input
                type='number'
                placeholder='Doctor ID (from employees table)'
              />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item label='Department' name='department_id'>
              <Select allowClear showSearch placeholder='Select department'>
                {departments.map((d: any) => (
                  <Option key={d.value || d.id} value={d.value || d.id}>
                    {d.label || d.name}
                  </Option>
                ))}
              </Select>
            </Form.Item>
          </Col>
        </Row>

        <Row gutter={16}>
          <Col span={8}>
            <Form.Item
              label='Schedule Type'
              name='schedule_type'
              rules={[{required: true}]}
            >
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
          <Col span={8}>
            <Form.Item label='Effective From' name='effective_from'>
              <DatePicker
                style={{width: '100%'}}
                format={DATABASE_DATE_FORMAT}
              />
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item label='Effective To' name='effective_to'>
              <DatePicker
                style={{width: '100%'}}
                format={DATABASE_DATE_FORMAT}
              />
            </Form.Item>
          </Col>
          <Col span={4}>
            <Form.Item label='Default' name='is_default' valuePropName='checked'>
              <Switch />
            </Form.Item>
          </Col>
          <Col span={4}>
            <Form.Item label='Active' name='status' valuePropName='checked'>
              <Switch checked={!!fields.status} />
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
          <Button
            type='dashed'
            icon={<PlusOutlined />}
            onClick={addSlot}
          >
            Add Slot
          </Button>
        }
      >
        <Form.Item name='slots' hidden>
          <Input />
        </Form.Item>

        {slots.length === 0 && (
          <div className='text-center text-muted py-4'>
            No slots yet. Click "Add Slot" to define when this doctor is
            available.
          </div>
        )}

        {slots.map((slot: any, index: number) => (
          <Row
            key={index}
            gutter={8}
            align='middle'
            className='mb-3 p-2 border rounded'
          >
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
                value={dayjs(slot.start_time, 'HH:mm')}
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
                value={dayjs(slot.end_time, 'HH:mm')}
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
                value={slot.max_patients}
                onChange={(v) => updateSlot(index, 'max_patients', v)}
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
                <Button
                  size='small'
                  type='text'
                  danger
                  icon={<DeleteOutlined />}
                />
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
    </div>
  )
}

export default DoctorScheduleForm

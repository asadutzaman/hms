import React from 'react'
import {Col, Form} from 'antd'
import {SelectProps} from 'antd/lib/select'
import DepartmentSelect from '../DepartmentSelect'
import DoctorSelect from '../DoctorSelect'
import {useLang} from 'src/app/hooks/useLang'

interface DependentProps {
  fieldName?: any
  fieldLabel?: any
  rules?: any
  gridCol?: any
}

interface Props extends SelectProps {
  formRef: any
  departmentProps?: DependentProps
  doctorProps?: DependentProps
}

/**
 * Dependent Department -> Doctor picker. Choosing a department narrows the
 * doctor list to that department (and clears any doctor already chosen).
 * The department is read with Form.useWatch so the doctor list refetches
 * reactively, which also works in forms that bind fields via formRef.
 */
const DepartmentDoctorDependentSelect: React.FC<Props> = (props) => {
  const {formRef} = props
  const {t} = useLang()

  const {
    fieldLabel: departmentLabel = 'Department',
    fieldName: departmentName = 'department_id',
    rules: departmentRules = null,
    gridCol: departmentGridCol = {xs: 24, md: 12},
  } = props.departmentProps || {}

  const {
    fieldLabel: doctorLabel = 'Doctor',
    fieldName: doctorName = 'doctor_id',
    rules: doctorRules = null,
    gridCol: doctorGridCol = {xs: 24, md: 12},
  } = props.doctorProps || {}

  const watchedDepartmentId = Form.useWatch(departmentName, formRef)
  const watchedDoctorId = Form.useWatch(doctorName, formRef)

  return (
    <>
      <Col {...departmentGridCol}>
        <Form.Item label={departmentLabel} name={departmentName} rules={departmentRules}>
          <DepartmentSelect
            departmentId={watchedDepartmentId}
            placeholder={t('Select Department')}
            allowClear
            onChange={(value: any) => {
              // Department drives the doctor list — reset the dependent field.
              formRef.setFieldsValue({[departmentName]: value ?? null, [doctorName]: null})
            }}
            onLoad={(value: any) => {
              formRef.setFieldsValue({[departmentName]: value})
            }}
          />
        </Form.Item>
      </Col>

      <Col {...doctorGridCol}>
        <Form.Item label={doctorLabel} name={doctorName} rules={doctorRules}>
          <DoctorSelect
            doctorId={watchedDoctorId}
            departmentId={watchedDepartmentId}
            placeholder={
              watchedDepartmentId ? t('Select Doctor') : t('Select a department first')
            }
            allowClear
            onChange={(value: any) => {
              formRef.setFieldsValue({[doctorName]: value ?? null})
            }}
          />
        </Form.Item>
      </Col>
    </>
  )
}

export default React.memo(DepartmentDoctorDependentSelect)

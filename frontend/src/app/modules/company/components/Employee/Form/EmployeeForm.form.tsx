import React, {FC, Fragment} from 'react'
import {Form, Input, Row, Col, Select} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import DesignationSelect from 'src/app/components/Dropdown/DesignationSelect'

const formItemLayout = {
  labelCol: {xs: {span: 12}, sm: {span: 12}},
  wrapperCol: {xs: {span: 24}, sm: {span: 24}},
}

const EmployeeAddOrEditForm: FC<any> = (props) => {
  const {Option} = Select
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props

  return (
    <Fragment>
      <div className='form-page-content form-page-content-employee pe-3'>
        <Form
          {...formItemLayout}
          layout='vertical'
          form={formRef}
          name='employeeForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={[16, 0]}>
            <Col span={12}>
              <Form.Item label='Name (English)' name='name_en' rules={rules.requiredMaxCharacter100}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Name (Bengali)' name='name_bn' rules={rules.maxCharacter100}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Employee ID' name='employee_id' rules={rules.requiredMaxCharacter100}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Designation' name='designation_id' rules={rules.required}>
                <DesignationSelect
                  designationId={formRef.getFieldValue('designation_id')}
                  placeholder='Select Designation'
                  onSelect={(value) => formRef.setFieldsValue({designation_id: value})}
                  onLoad={(value) => formRef.setFieldsValue({designation_id: value})}
                />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Gender' name='gender' rules={rules.required}>
                <Select placeholder='-- Select --'>
                  <Option value='male'>Male</Option>
                  <Option value='female'>Female</Option>
                  <Option value='other'>Other</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Mobile' name='mobile' rules={rules.maxCharacter100}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Date of Birth' name='dob'>
                <Input type='date' />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Joining Date' name='joining_date'>
                <Input type='date' />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Employee Type' name='employee_type' rules={rules.maxCharacter100}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Employee Category' name='employee_category' rules={rules.maxCharacter100}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label='Status' name='status'>
                <Select placeholder='-- Select --'>
                  <Option value={1}>Active</Option>
                  <Option value={0}>Inactive</Option>
                </Select>
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}

export default React.memo(EmployeeAddOrEditForm)

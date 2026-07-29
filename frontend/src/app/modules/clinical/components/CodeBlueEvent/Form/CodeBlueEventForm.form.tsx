import React, {FC, Fragment} from 'react'
import {Form, Input, InputNumber, DatePicker, Row, Col, Select} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'

const formItemLayout = {labelCol: {xs: {span: 6}, sm: {span: 6}}, wrapperCol: {xs: {span: 24}, sm: {span: 24}}}

const CodeBlueEventAddOrEditForm: FC<any> = (props) => {
  const {Option} = Select
  const {TextArea} = Input
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  return (
    <Fragment>
      <div className='form-page-content pe-3'>
        <Form {...formItemLayout} layout='vertical' form={formRef} name='code-blueForm' scrollToFirstError={true}
          initialValues={initialValues} onValuesChange={handleChange} onFinish={handleSubmit} onFinishFailed={handleSubmitFailed}>
          <Row gutter={24}>
            <Col span={24}>
              <Form.Item label='Event Type' name='event_type'>
                <Select placeholder='--Select--'>
                  <Option key='code_blue' value='code_blue'>code_blue</Option>
                  <Option key='rapid_response' value='rapid_response'>rapid_response</Option>
                </Select>
              </Form.Item>
              <Form.Item label='Patient ID' name='patient_id'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
              <Form.Item label='Ward ID' name='ward_id'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
              <Form.Item label='Location' name='location'>
                <Input />
              </Form.Item>
              <Form.Item label='State' name='state'>
                <Select placeholder='--Select--'>
                  <Option key='active' value='active'>active</Option>
                  <Option key='responded' value='responded'>responded</Option>
                  <Option key='resolved' value='resolved'>resolved</Option>
                  <Option key='cancelled' value='cancelled'>cancelled</Option>
                </Select>
              </Form.Item>
              <Form.Item label='Severity' name='severity'>
                <Input />
              </Form.Item>
              <Form.Item label='Reason' name='reason'>
                <TextArea autoSize={{minRows: 2, maxRows: 5}} />
              </Form.Item>
              <Form.Item label='Status' name='status'>
                <Select placeholder='--Select--'>
                  <Option key='status-active' value={1}>Active</Option>
                  <Option key='status-inactive' value={0}>InActive</Option>
                </Select>
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}
export default React.memo(CodeBlueEventAddOrEditForm)

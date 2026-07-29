import React, {FC, Fragment} from 'react'
import {Form, Input, InputNumber, DatePicker, Row, Col, Select} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'

const formItemLayout = {labelCol: {xs: {span: 6}, sm: {span: 6}}, wrapperCol: {xs: {span: 24}, sm: {span: 24}}}

const ClinicalJobAddOrEditForm: FC<any> = (props) => {
  const {Option} = Select
  const {TextArea} = Input
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  return (
    <Fragment>
      <div className='form-page-content pe-3'>
        <Form {...formItemLayout} layout='vertical' form={formRef} name='clinical-jobsForm' scrollToFirstError={true}
          initialValues={initialValues} onValuesChange={handleChange} onFinish={handleSubmit} onFinishFailed={handleSubmitFailed}>
          <Row gutter={24}>
            <Col span={24}>
              <Form.Item label='Title' name='title'>
                <Input />
              </Form.Item>
              <Form.Item label='Type' name='job_type'>
                <Input />
              </Form.Item>
              <Form.Item label='Priority' name='priority'>
                <Select placeholder='--Select--'>
                  <Option key='routine' value='routine'>routine</Option>
                  <Option key='urgent' value='urgent'>urgent</Option>
                  <Option key='critical' value='critical'>critical</Option>
                </Select>
              </Form.Item>
              <Form.Item label='Patient ID' name='patient_id'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
              <Form.Item label='Ward ID' name='ward_id'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
              <Form.Item label='Role' name='role_type'>
                <Select placeholder='--Select--'>
                  <Option key='doctor' value='doctor'>doctor</Option>
                  <Option key='nurse' value='nurse'>nurse</Option>
                </Select>
              </Form.Item>
              <Form.Item label='State' name='state'>
                <Select placeholder='--Select--'>
                  <Option key='open' value='open'>open</Option>
                  <Option key='claimed' value='claimed'>claimed</Option>
                  <Option key='done' value='done'>done</Option>
                  <Option key='cancelled' value='cancelled'>cancelled</Option>
                </Select>
              </Form.Item>
              <Form.Item label='Due At' name='due_at'>
                <DatePicker style={{width: '100%'}} />
              </Form.Item>
              <Form.Item label='Description' name='description'>
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
export default React.memo(ClinicalJobAddOrEditForm)

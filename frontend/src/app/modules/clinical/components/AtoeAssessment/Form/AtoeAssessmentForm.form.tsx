import React, {FC, Fragment} from 'react'
import {Form, Input, InputNumber, DatePicker, Row, Col, Select} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'

const formItemLayout = {labelCol: {xs: {span: 6}, sm: {span: 6}}, wrapperCol: {xs: {span: 24}, sm: {span: 24}}}

const AtoeAssessmentAddOrEditForm: FC<any> = (props) => {
  const {Option} = Select
  const {TextArea} = Input
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  return (
    <Fragment>
      <div className='form-page-content pe-3'>
        <Form {...formItemLayout} layout='vertical' form={formRef} name='atoe-assessmentsForm' scrollToFirstError={true}
          initialValues={initialValues} onValuesChange={handleChange} onFinish={handleSubmit} onFinishFailed={handleSubmitFailed}>
          <Row gutter={24}>
            <Col span={24}>
              <Form.Item label='Patient ID' name='patient_id'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
              <Form.Item label='NEWS2' name='news2_score'>
                <InputNumber style={{width: '100%'}} />
              </Form.Item>
              <Form.Item label='Airway' name='airway'>
                <TextArea autoSize={{minRows: 2, maxRows: 5}} />
              </Form.Item>
              <Form.Item label='Breathing' name='breathing'>
                <TextArea autoSize={{minRows: 2, maxRows: 5}} />
              </Form.Item>
              <Form.Item label='Circulation' name='circulation'>
                <TextArea autoSize={{minRows: 2, maxRows: 5}} />
              </Form.Item>
              <Form.Item label='Disability' name='disability'>
                <TextArea autoSize={{minRows: 2, maxRows: 5}} />
              </Form.Item>
              <Form.Item label='Exposure' name='exposure'>
                <TextArea autoSize={{minRows: 2, maxRows: 5}} />
              </Form.Item>
              <Form.Item label='Impression' name='impression'>
                <TextArea autoSize={{minRows: 2, maxRows: 5}} />
              </Form.Item>
              <Form.Item label='Plan' name='plan'>
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
export default React.memo(AtoeAssessmentAddOrEditForm)

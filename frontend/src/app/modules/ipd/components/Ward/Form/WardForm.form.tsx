import React, {FC, Fragment} from 'react'
import {Form, Input, Select, Row, Col} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import BranchSelect from 'src/app/components/Dropdown/BranchSelect'
import {useLang} from 'src/app/hooks/useLang'

const formItemLayout = {
  labelCol: {
    xs: {span: 6},
    sm: {span: 6},
  },
  wrapperCol: {
    xs: {span: 24},
    sm: {span: 24},
  },
}

const wardTypeOptions = [
  {value: 'general', label: 'General'},
  {value: 'semi_private', label: 'Semi Private'},
  {value: 'private', label: 'Private'},
  {value: 'icu', label: 'ICU'},
  {value: 'emergency', label: 'Emergency'},
]

const WardAddOrEditForm: FC<any> = (props) => {
  const {TextArea} = Input
  const {Option} = Select
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()
  return (
    <Fragment>
      <div className='form-page-content form-page-content-example pe-3'>
        <Form
          {...formItemLayout}
          layout='vertical'
          form={formRef}
          name='wardForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={24}>
            <Col span={24}>
              <Form.Item label={t('Branch')} name='branch_id'>
                <BranchSelect
                  branchId={formRef.getFieldValue('branch_id')}
                  placeholder={t('Select Branch')}
                  onSelect={(value: any) => formRef.setFieldsValue({branch_id: value})}
                  onLoad={(value: any) => formRef.setFieldsValue({branch_id: value})}
                />
              </Form.Item>

              <Form.Item label={t('Name')} name='name' rules={rules.required}>
                <Input />
              </Form.Item>

              <Form.Item label={t('Ward Type')} name='ward_type'>
                <Select placeholder={t('Select Ward Type')}>
                  {wardTypeOptions.map((option) => (
                    <Option key={option.value} value={option.value}>
                      {t(option.label)}
                    </Option>
                  ))}
                </Select>
              </Form.Item>

              <Form.Item label={t('Floor')} name='floor'>
                <Input />
              </Form.Item>

              <Form.Item label={t('Description')} name='description'>
                <TextArea />
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}
export default React.memo(WardAddOrEditForm)

import React, {FC, Fragment} from 'react'
import {Form, Input, Select, InputNumber, Row, Col} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input
const {Option} = Select

const InsuranceCompanyAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()
  return (
    <Fragment>
      <div className='form-page-content pe-3'>
        <Form
          layout='vertical'
          form={formRef}
          name='insuranceCompanyForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={24}>
            <Col span={12}>
              <Form.Item label={t('Code')} name='code' rules={rules.required}>
                <Input placeholder={t('e.g. GRD-LIFE')} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Name')} name='name' rules={rules.required}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Type')} name='tpa_type'>
                <Select placeholder={t('Select Type')}>
                  <Option value='insurer'>{t('Insurer')}</Option>
                  <Option value='corporate'>{t('Corporate / TPA')}</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Credit Limit')} name='credit_limit'>
                <InputNumber min={0} precision={2} style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Contact Person')} name='contact_person'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Phone')} name='phone'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Email')} name='email' rules={[{type: 'email', message: t('Invalid email address')}]}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item label={t('Address')} name='address'>
                <TextArea rows={2} />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item label={t('Description')} name='description'>
                <TextArea rows={2} />
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}
export default React.memo(InsuranceCompanyAddOrEditForm)

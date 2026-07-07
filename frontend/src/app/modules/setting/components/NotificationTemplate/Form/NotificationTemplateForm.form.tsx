import React, {FC, Fragment} from 'react'
import {Form, Input, Select, Row, Col} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input
const {Option} = Select

const NotificationTemplateAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()
  return (
    <Fragment>
      <div className='form-page-content pe-3'>
        <Form
          layout='vertical'
          form={formRef}
          name='notificationTemplateForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={24}>
            <Col span={12}>
              <Form.Item
                label={t('Key')}
                name='key'
                rules={rules.required}
                extra={t('Dotted convention, e.g. critical_lab_value.email')}
              >
                <Input placeholder='event_key.channel' />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Name')} name='name' rules={rules.required}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Channel')} name='channel' rules={rules.required}>
                <Select placeholder={t('Select Channel')}>
                  <Option value='in_app'>{t('In-App')}</Option>
                  <Option value='email'>{t('Email')}</Option>
                  <Option value='sms'>{t('SMS')}</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Subject Template')} name='subject_template'>
                <Input placeholder={t('Only used for email channel')} />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item
                label={t('Body Template')}
                name='body_template'
                rules={rules.required}
                extra={t('Use {{key}} placeholders, e.g. {{patient_name}}')}
              >
                <TextArea rows={5} />
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}
export default React.memo(NotificationTemplateAddOrEditForm)

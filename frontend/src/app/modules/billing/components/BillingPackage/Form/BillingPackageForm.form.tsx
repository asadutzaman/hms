import React, {FC, Fragment} from 'react'
import {Form, Input, Select, InputNumber, Row, Col} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input
const {Option} = Select

const BillingPackageAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()
  return (
    <Fragment>
      <div className='form-page-content pe-3'>
        <Form
          layout='vertical'
          form={formRef}
          name='billingPackageForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={24}>
            <Col span={12}>
              <Form.Item label={t('Code')} name='code' rules={rules.required}>
                <Input placeholder={t('e.g. PKG-DELIVERY-01')} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Name')} name='name' rules={rules.required}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Applies To')} name='package_type'>
                <Select>
                  <Option value='opd'>{t('OPD')}</Option>
                  <Option value='ipd'>{t('IPD')}</Option>
                  <Option value='both'>{t('Both')}</Option>
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Fixed Price')} name='fixed_price' rules={rules.required}>
                <InputNumber min={0} precision={2} style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item label={t('Description')} name='description'>
                <TextArea rows={3} />
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}
export default React.memo(BillingPackageAddOrEditForm)

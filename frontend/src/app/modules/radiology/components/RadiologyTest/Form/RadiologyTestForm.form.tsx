import React, {FC, Fragment} from 'react'
import {Form, Input, Select, InputNumber, Row, Col} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input
const {Option} = Select

const modalityOptions = [
  {value: 'xray', label: 'X-Ray'},
  {value: 'ct', label: 'CT Scan'},
  {value: 'mri', label: 'MRI'},
  {value: 'ultrasound', label: 'Ultrasound'},
  {value: 'mammography', label: 'Mammography'},
  {value: 'fluoroscopy', label: 'Fluoroscopy'},
  {value: 'other', label: 'Other'},
]

const RadiologyTestAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()
  return (
    <Fragment>
      <div className='form-page-content pe-3'>
        <Form
          layout='vertical'
          form={formRef}
          name='radiologyTestForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={24}>
            <Col span={12}>
              <Form.Item label={t('Code')} name='code' rules={rules.required}>
                <Input placeholder={t('e.g. RAD-001')} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Name')} name='name' rules={rules.required}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Modality')} name='modality' rules={rules.required}>
                <Select placeholder={t('Select Modality')}>
                  {modalityOptions.map((o) => (
                    <Option key={o.value} value={o.value}>
                      {t(o.label)}
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Body Part')} name='body_part'>
                <Input placeholder={t('e.g. Chest, Brain')} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('TAT (hours)')} name='tat_hours'>
                <InputNumber min={0} style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Default Price')} name='default_price'>
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
export default React.memo(RadiologyTestAddOrEditForm)

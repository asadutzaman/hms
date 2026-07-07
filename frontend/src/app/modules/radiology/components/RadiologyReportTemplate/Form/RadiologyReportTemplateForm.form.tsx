import React, {FC, Fragment} from 'react'
import {Form, Input, Select, Row, Col} from 'antd'
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

const RadiologyReportTemplateAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()
  return (
    <Fragment>
      <div className='form-page-content pe-3'>
        <Form
          layout='vertical'
          form={formRef}
          name='radiologyReportTemplateForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={24}>
            <Col span={12}>
              <Form.Item label={t('Name')} name='name' rules={rules.required}>
                <Input placeholder={t('e.g. Chest X-Ray Normal')} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Modality')} name='modality'>
                <Select placeholder={t('Select Modality')} allowClear>
                  {modalityOptions.map((o) => (
                    <Option key={o.value} value={o.value}>
                      {t(o.label)}
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item label={t('Body Part')} name='body_part'>
                <Input placeholder={t('e.g. Chest, Brain')} />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item label={t('Findings Template')} name='findings_template'>
                <TextArea rows={5} placeholder={t('Pre-fillable common findings text')} />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item label={t('Impression Template')} name='impression_template'>
                <TextArea rows={3} />
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}
export default React.memo(RadiologyReportTemplateAddOrEditForm)

import React, {FC, Fragment} from 'react'
import {Form, Input, Select, InputNumber, Row, Col} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import {useLang} from 'src/app/hooks/useLang'

const {TextArea} = Input
const {Option} = Select

const categoryOptions = [
  {value: 'haematology', label: 'Haematology'},
  {value: 'biochemistry', label: 'Biochemistry'},
  {value: 'urine', label: 'Urinalysis'},
  {value: 'microbiology', label: 'Microbiology'},
  {value: 'serology', label: 'Serology'},
  {value: 'radiology', label: 'Radiology'},
  {value: 'cardiology', label: 'Cardiology'},
]

const LabTestAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()
  return (
    <Fragment>
      <div className='form-page-content pe-3'>
        <Form
          layout='vertical'
          form={formRef}
          name='labTestForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={24}>
            <Col span={12}>
              <Form.Item label={t('Code')} name='code' rules={rules.required}>
                <Input placeholder={t('e.g. HEM-001')} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Name')} name='name' rules={rules.required}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Category')} name='category'>
                <Select placeholder={t('Select Category')} allowClear>
                  {categoryOptions.map((o) => (
                    <Option key={o.value} value={o.value}>
                      {t(o.label)}
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Sample Type')} name='sample_type'>
                <Input placeholder={t('e.g. Serum, EDTA Blood')} />
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
export default React.memo(LabTestAddOrEditForm)

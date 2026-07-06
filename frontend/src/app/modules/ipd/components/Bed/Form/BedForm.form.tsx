import React, {FC, Fragment} from 'react'
import {Form, Input, InputNumber, Row, Col} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import WardSelect from 'src/app/components/Dropdown/WardSelect'
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

// NOTE: `bed_status` is intentionally not exposed here — like OpdVisit.status,
// it is system-managed (driven by admission/discharge workflow elsewhere) and
// defaults to 'vacant' on the backend when a bed is created.
const BedAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()
  return (
    <Fragment>
      <div className='form-page-content form-page-content-example pe-3'>
        <Form
          {...formItemLayout}
          layout='vertical'
          form={formRef}
          name='bedForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={24}>
            <Col span={24}>
              <Form.Item label={t('Ward')} name='ward_id' rules={rules.required}>
                <WardSelect
                  wardId={formRef.getFieldValue('ward_id')}
                  placeholder={t('Select Ward')}
                  onSelect={(value: any) => formRef.setFieldsValue({ward_id: value})}
                  onLoad={(value: any) => formRef.setFieldsValue({ward_id: value})}
                />
              </Form.Item>

              <Form.Item label={t('Bed Number')} name='bed_number' rules={rules.required}>
                <Input />
              </Form.Item>

              <Form.Item label={t('Bed Type')} name='bed_type'>
                <Input placeholder={t('e.g. Manual, Electric, Cot')} />
              </Form.Item>

              <Form.Item label={t('Daily Rate')} name='daily_rate'>
                <InputNumber min={0} style={{width: '100%'}} />
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}
export default React.memo(BedAddOrEditForm)

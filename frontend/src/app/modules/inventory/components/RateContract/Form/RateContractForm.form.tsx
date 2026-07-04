import React, {FC, Fragment} from 'react'
import {Form, Row, Col, DatePicker, InputNumber} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import SupplierSelect from 'src/app/components/Dropdown/SupplierSelect'
import ItemSelect from 'src/app/components/Dropdown/LiveSearch/ItemSelect'
import {useLang} from 'src/app/hooks/useLang'

const formItemLayout = {
  labelCol: {xs: {span: 24}, sm: {span: 24}},
  wrapperCol: {xs: {span: 24}, sm: {span: 24}},
}

const RateContractAddOrEditForm: FC<any> = (props) => {
  const {formRef, initialValues, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()

  return (
    <Fragment>
      <div className='form-page-content form-page-content-rate-contract pe-3'>
        <Form
          {...formItemLayout}
          layout='vertical'
          form={formRef}
          name='rateContractForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Row gutter={24}>
            <Col span={12}>
              <Form.Item label={t('Supplier')} name='supplier_id' rules={rules.required}>
                <SupplierSelect
                  supplierId={formRef.getFieldValue('supplier_id')}
                  placeholder={t('Select Supplier')}
                  onSelect={(value: any) => formRef.setFieldsValue({supplier_id: value})}
                  onLoad={(value: any) => formRef.setFieldsValue({supplier_id: value})}
                />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Item')} name='item_id' rules={rules.required}>
                <ItemSelect
                  itemNameCode={formRef.getFieldValue('item_id')}
                  placeholder={t('Search by Item Name/Code')}
                  onLoad={(value: any) => formRef.setFieldsValue({item_id: value})}
                  onSelect={(value: any) => formRef.setFieldsValue({item_id: value})}
                />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col span={12}>
              <Form.Item label={t('Contract Price')} name='contract_price' rules={rules.required}>
                <InputNumber min={0} style={{width: '100%'}} />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col span={12}>
              <Form.Item label={t('Valid From')} name='valid_from' rules={rules.required}>
                <DatePicker style={{width: '100%'}} placeholder={t('Select Date')} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Valid To')} name='valid_to' rules={rules.required}>
                <DatePicker style={{width: '100%'}} placeholder={t('Select Date')} />
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}
export default React.memo(RateContractAddOrEditForm)

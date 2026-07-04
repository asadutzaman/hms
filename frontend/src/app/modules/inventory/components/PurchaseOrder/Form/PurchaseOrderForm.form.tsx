import React, {FC, Fragment} from 'react'
import {Form, Input, Row, Col, Select, DatePicker, Divider, Spin} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import PurchaseOrderItemAddMore from '../AddMore/PurchaseOrderItem.addMore'
import ItemSelect from 'src/app/components/Dropdown/LiveSearch/ItemSelect'
import {useLang} from 'src/app/hooks/useLang'
import SupplierSelect from 'src/app/components/Dropdown/SupplierSelect'

const formItemLayout = {
  labelCol: {
    xs: {span: 12},
    sm: {span: 12},
  },
  wrapperCol: {
    xs: {span: 24},
    sm: {span: 24},
  },
}

const PurchaseOrderAddOrEditForm: FC<any> = (props) => {
  const {Option} = Select
  const {
    formRef,
    initialValues,
    handleChange,
    handleSubmit,
    handleSubmitFailed,
    poItemList,
    setPoItemList,
    handleItemSelect,
    isLoadingPoItem,
  } = props
  const {t} = useLang()

  return (
    <Fragment>
      <div className='form-page-content form-page-content-example pe-3'>
        <Form
          {...formItemLayout}
          layout='vertical'
          form={formRef}
          name='purchaseOrderForm'
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
                  onSelect={(value, option) => {
                    formRef.setFieldsValue({supplier_id: value})
                  }}
                  onLoad={(value) => {
                    formRef.setFieldsValue({supplier_id: value})
                  }}
                />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Order Date')} name='order_date' rules={rules.required}>
                <DatePicker style={{width: '100%'}} placeholder={t('Select Date')} />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col span={12}>
              <Form.Item label={t('Expected Delivery Date')} name='expected_delivery_date'>
                <DatePicker style={{width: '100%'}} placeholder={t('Select Date')} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item label={t('Notes')} name='notes'>
                <Input.TextArea />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col span={24}>
              <Form.Item label={t('Inventory Item')} labelCol={{span: 24}} name={'item_id'}>
                <ItemSelect
                  itemNameCode={formRef.getFieldValue('item_id')}
                  placeholder={t('Search by Item Name/Code (type min 3 digit)')}
                  onLoad={(value) => {
                    formRef.setFieldsValue({item_id: value})
                  }}
                  onSelect={(value, option) => handleItemSelect(value, option)}
                  onChange={(value, option) => {
                    formRef.setFieldsValue({item_id: value})
                  }}
                />
              </Form.Item>
            </Col>
          </Row>

          <Divider orientation='left' orientationMargin='0'>
            {t('Particulars of Purchase Order')}
          </Divider>
          {isLoadingPoItem && (
            <>
              <Spin size='small' spinning={isLoadingPoItem} />
              &nbsp;
            </>
          )}
          {isLoadingPoItem === false && (
            <Row gutter={[16, 16]}>
              <Col sm={24} xs={24}>
                <PurchaseOrderItemAddMore addMoreItemList={poItemList} setAddMoreItemList={setPoItemList} />
              </Col>
            </Row>
          )}

          <Row gutter={24}>
            <Col span={24}>
              <Form.Item label={t('Process Status')} name='process_status' rules={rules.required}>
                <Select placeholder={t('Select process status')}>
                  <Option value='DRAFT'>{t('Draft')}</Option>
                  <Option value='SUBMITTED'>{t('Submit')}</Option>
                </Select>
              </Form.Item>
            </Col>
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}
export default React.memo(PurchaseOrderAddOrEditForm)

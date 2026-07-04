import React, {FC, Fragment, useEffect, useState} from 'react'
import {Form, Input, Row, Col, Select, InputNumber, Switch, Divider} from 'antd'
import {rules} from 'src/app/components/Validation/Form.validate'
import UnitSelect from 'src/app/components/Dropdown/UnitSelect'
import ItemCategorySelect from 'src/app/components/Dropdown/ItemCategorySelect'
import LogisticSelect from 'src/app/components/Dropdown/LogisticSelect'
import BrandSelect from 'src/app/components/Dropdown/BrandSelect'
import {DrugApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

const formItemLayout = {
  labelCol: {xs: {span: 24}, sm: {span: 24}},
  wrapperCol: {xs: {span: 24}, sm: {span: 24}},
}

const DrugAddOrEditForm: FC<any> = (props) => {
  const {Option} = Select
  const {formRef, initialValues, loading, handleChange, handleSubmit, handleSubmitFailed} = props
  const {t} = useLang()
  const [generics, setGenerics] = useState<any[]>([])
  const isControlled = Form.useWatch('is_controlled', formRef)

  useEffect(() => {
    DrugApi.generics()
      .then((res: any) => setGenerics(res?.data?.data || res?.data || []))
      .catch(() => setGenerics([]))
  }, [])

  return (
    <Fragment>
      <div className='form-page-content form-page-content-drug pe-3'>
        <Form
          {...formItemLayout}
          layout='vertical'
          form={formRef}
          name='drugForm'
          scrollToFirstError={true}
          initialValues={initialValues}
          onValuesChange={handleChange}
          onFinish={handleSubmit}
          onFinishFailed={handleSubmitFailed}
        >
          <Divider orientation='left' orientationMargin='0'>
            {t('Item / Product Master')}
          </Divider>
          <Row gutter={24}>
            <Col span={8}>
              <Form.Item label={t('Item Name (EN)')} name='name_en' rules={rules.required}>
                <Input placeholder={t('e.g. Napa 500')} />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label={t('Item Name (BN)')} name='name_bn' rules={rules.required}>
                <Input />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label={t('Logistic')} name='logistic_id' rules={rules.required}>
                <LogisticSelect
                  logisticId={formRef.getFieldValue('logistic_id')}
                  placeholder={t('Select Logistic')}
                  onSelect={(value: any) => formRef.setFieldsValue({logistic_id: value})}
                  onLoad={(value: any) => formRef.setFieldsValue({logistic_id: value})}
                />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col span={8}>
              <Form.Item label={t('Item Category')} name='item_category_id' rules={rules.required}>
                <ItemCategorySelect
                  itemCategoryId={formRef.getFieldValue('item_category_id')}
                  placeholder={t('Select Item Category')}
                  onSelect={(value: any) => formRef.setFieldsValue({item_category_id: value})}
                  onLoad={(value: any) => formRef.setFieldsValue({item_category_id: value})}
                />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label={t('Brand (Manufacturer)')} name='brand_id' rules={rules.required}>
                <BrandSelect
                  brandId={formRef.getFieldValue('brand_id')}
                  placeholder={t('Select Manufacturer/Brand')}
                  onSelect={(value: any) => formRef.setFieldsValue({brand_id: value})}
                />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label={t('Base Unit')} name='base_unit_id' rules={rules.required}>
                <UnitSelect
                  unitId={formRef.getFieldValue('base_unit_id')}
                  placeholder={t('Select Base Unit')}
                  onSelect={(value: any) => formRef.setFieldsValue({base_unit_id: value})}
                />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col span={8}>
              <Form.Item label={t('Reorder Qty')} name='reorder_qty' rules={rules.required}>
                <InputNumber min={0} style={{width: '100%'}} />
              </Form.Item>
            </Col>
            <Col span={16}>
              <Form.Item label={t('Description')} name='description'>
                <Input />
              </Form.Item>
            </Col>
          </Row>

          <Divider orientation='left' orientationMargin='0'>
            {t('Drug Details')}
          </Divider>
          <Row gutter={24}>
            <Col span={8}>
              <Form.Item label={t('Generic Name')} name='generic_name' rules={rules.required}>
                <Input placeholder={t('e.g. Paracetamol')} />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label={t('Brand Name')} name='brand_name'>
                <Input placeholder={t('e.g. Napa')} />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label={t('Strength')} name='strength'>
                <Input placeholder={t('e.g. 500mg')} />
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col span={8}>
              <Form.Item label={t('Dosage Form')} name='dosage_form' rules={rules.required}>
                <Select placeholder={t('Select Dosage Form')}>
                  {['tablet', 'capsule', 'syrup', 'injection', 'ointment', 'drops', 'inhaler', 'other'].map(
                    (f) => (
                      <Option key={f} value={f} className='text-capitalize'>
                        {t(f.charAt(0).toUpperCase() + f.slice(1))}
                      </Option>
                    )
                  )}
                </Select>
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label={t('HSN Code')} name='hsn_code'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item label={t('Map to Generic')} name='generic_drug_id' tooltip={t('Leave blank if this IS the generic/reference drug')}>
                <Select allowClear showSearch placeholder={t('Select Generic Drug')} optionFilterProp='children'>
                  {generics.map((g: any) => (
                    <Option key={g.id} value={g.id}>
                      {g.generic_name} {g.name_en ? `(${g.name_en})` : ''}
                    </Option>
                  ))}
                </Select>
              </Form.Item>
            </Col>
          </Row>

          <Row gutter={24}>
            <Col span={8}>
              <Form.Item label={t('Controlled Substance')} name='is_controlled' valuePropName='checked'>
                <Switch />
              </Form.Item>
            </Col>
            {isControlled && (
              <Col span={8}>
                <Form.Item label={t('Controlled Schedule')} name='controlled_schedule' rules={rules.required}>
                  <Input placeholder={t('e.g. Schedule H1')} />
                </Form.Item>
              </Col>
            )}
          </Row>
        </Form>
      </div>
    </Fragment>
  )
}

export default React.memo(DrugAddOrEditForm)

import React, {FC, useState} from 'react'
import {Button, Form, Input, InputNumber} from 'antd'
import {VendorQuoteApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'
import SupplierSelect from 'src/app/components/Dropdown/SupplierSelect'
import ItemSelect from 'src/app/components/Dropdown/LiveSearch/ItemSelect'

interface Props {
  onSaved: () => void
}

const AddQuoteModal: FC<Props> = ({onSaved}) => {
  const {t} = useLang()
  const [form] = Form.useForm()
  const [saving, setSaving] = useState(false)

  const handleSave = async () => {
    try {
      const values = await form.validateFields()
      setSaving(true)
      await VendorQuoteApi.create({...values, submitted_at: new Date().toISOString()})
      Message.success('Vendor quote added')
      form.resetFields()
      onSaved()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to add vendor quote')
    } finally {
      setSaving(false)
    }
  }

  return (
    <Form form={form} layout='vertical'>
      <Form.Item name='item_id' label={t('Item')} rules={[{required: true}]}>
        <ItemSelect
          itemNameCode={form.getFieldValue('item_id')}
          placeholder={t('Search by Item Name/Code')}
          onSelect={(value: any) => form.setFieldsValue({item_id: value})}
        />
      </Form.Item>
      <Form.Item name='supplier_id' label={t('Supplier')} rules={[{required: true}]}>
        <SupplierSelect
          supplierId={form.getFieldValue('supplier_id')}
          placeholder={t('Select Supplier')}
          onSelect={(value: any) => form.setFieldsValue({supplier_id: value})}
        />
      </Form.Item>
      <Form.Item name='quoted_unit_price' label={t('Quoted Unit Price')} rules={[{required: true}]}>
        <InputNumber min={0} style={{width: '100%'}} />
      </Form.Item>
      <Form.Item name='quoted_delivery_days' label={t('Delivery Days')}>
        <InputNumber min={0} style={{width: '100%'}} />
      </Form.Item>
      <Form.Item name='notes' label={t('Notes')}>
        <Input.TextArea rows={2} />
      </Form.Item>
      <div className='d-flex justify-content-end'>
        <Button type='primary' onClick={handleSave} loading={saving}>
          {t('Save Quote')}
        </Button>
      </div>
    </Form>
  )
}

export default AddQuoteModal

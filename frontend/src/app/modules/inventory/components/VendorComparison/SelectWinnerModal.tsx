import React, {FC, useState} from 'react'
import {Button, DatePicker, Form, Switch} from 'antd'
import {VendorQuoteApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'

interface Props {
  quote: any
  onSaved: () => void
}

const SelectWinnerModal: FC<Props> = ({quote, onSaved}) => {
  const {t} = useLang()
  const [form] = Form.useForm()
  const [saving, setSaving] = useState(false)
  const [createContract, setCreateContract] = useState(true)

  const handleSave = async () => {
    try {
      const values = createContract ? await form.validateFields() : {}
      setSaving(true)
      await VendorQuoteApi.selectWinner(quote.id, {
        create_rate_contract: createContract,
        valid_from: values.valid_from ? values.valid_from.format('YYYY-MM-DD') : undefined,
        valid_to: values.valid_to ? values.valid_to.format('YYYY-MM-DD') : undefined,
      })
      Message.success('Winning quote selected')
      onSaved()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error('Failed to select winner')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div>
      <p>
        {t('Supplier')}: <strong>{quote.supplier_name}</strong> — {t('Price')}: <strong>{quote.quoted_unit_price}</strong>
      </p>
      <div className='d-flex align-items-center mb-4'>
        <Switch checked={createContract} onChange={setCreateContract} className='me-3' />
        <span>{t('Also create a Rate Contract from this quote')}</span>
      </div>
      {createContract && (
        <Form form={form} layout='vertical'>
          <Form.Item name='valid_from' label={t('Valid From')} rules={[{required: true}]}>
            <DatePicker style={{width: '100%'}} />
          </Form.Item>
          <Form.Item name='valid_to' label={t('Valid To')} rules={[{required: true}]}>
            <DatePicker style={{width: '100%'}} />
          </Form.Item>
        </Form>
      )}
      <div className='d-flex justify-content-end'>
        <Button type='primary' onClick={handleSave} loading={saving}>
          {t('Confirm')}
        </Button>
      </div>
    </div>
  )
}

export default SelectWinnerModal

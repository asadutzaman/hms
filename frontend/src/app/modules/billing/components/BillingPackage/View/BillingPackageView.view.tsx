import React, {FC, useEffect, useState} from 'react'
import {Input, InputNumber, Button, notification, Popconfirm} from 'antd'
import {PlusOutlined, DeleteOutlined, SaveOutlined} from '@ant-design/icons'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {BillingPackageAction} from '../Actions/BillingPackage.actions'
import {BillingPackageApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

interface PackageItemRow {
  item_type: string
  description: string
  default_quantity: number
  notional_unit_price: number | null
}

const emptyItem = (): PackageItemRow => ({
  item_type: 'other',
  description: '',
  default_quantity: 1,
  notional_unit_price: null,
})

const BillingPackageView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  const [items, setItems] = useState<PackageItemRow[]>([])
  const [saving, setSaving] = useState(false)

  useEffect(() => {
    const existing = (itemData?.items || []).map((i: any) => ({
      item_type: i.item_type,
      description: i.description,
      default_quantity: i.default_quantity ?? 1,
      notional_unit_price: i.notional_unit_price,
    }))
    setItems(existing)
  }, [itemData?.id, itemData?.items])

  const addItem = () => setItems([...items, emptyItem()])
  const removeItem = (idx: number) => setItems(items.filter((_, i) => i !== idx))
  const updateItem = (idx: number, field: keyof PackageItemRow, value: any) => {
    const next = [...items]
    ;(next[idx] as any)[field] = value
    setItems(next)
  }

  const handleSaveItems = async () => {
    if (!itemData?.id) return
    setSaving(true)
    try {
      await BillingPackageApi.updateItems(itemData.id, {items})
      notification.success({message: t('Package inclusions saved successfully')})
      handleCallbackFunc(null, 'reloadView')
    } catch (e: any) {
      notification.error({
        message: t('Failed to save inclusions'),
        description: e?.response?.data?.message || e?.message,
      })
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction
            entityId={itemData.id}
            actionItem={BillingPackageAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={BillingPackageAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>

      <div className='table-responsive mb-8'>
        <table className='table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4'>
          <tbody>
            <tr>
              <td width={'20%'}>{t('Code')}</td>
              <td width={'5%'}>:</td>
              <td width={'75%'}>{itemData.code}</td>
            </tr>
            <tr>
              <td>{t('Name')}</td>
              <td>:</td>
              <td>{itemData.name}</td>
            </tr>
            <tr>
              <td>{t('Applies To')}</td>
              <td>:</td>
              <td>{(itemData.package_type || '').toUpperCase()}</td>
            </tr>
            <tr>
              <td>{t('Fixed Price')}</td>
              <td>:</td>
              <td>{itemData.fixed_price}</td>
            </tr>
            <tr>
              <td>{t('Description')}</td>
              <td>:</td>
              <td>{itemData.description}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div className='d-flex justify-content-between align-items-center mb-4'>
        <h4 className='mb-0'>{t('Inclusions')}</h4>
        <div>
          <Button icon={<PlusOutlined />} onClick={addItem} className='me-2'>
            {t('Add Inclusion')}
          </Button>
          <Button type='primary' icon={<SaveOutlined />} loading={saving} onClick={handleSaveItems}>
            {t('Save Inclusions')}
          </Button>
        </div>
      </div>

      <div className='text-muted fs-8 mb-3'>
        {t('Inclusions are informational only — the package always bills at its fixed price above.')}
      </div>

      {items.length === 0 && <div className='text-muted mb-4'>{t('No inclusions defined yet.')}</div>}

      {items.map((item, idx) => (
        <div key={idx} className='row g-3 align-items-end mb-3'>
          <div className='col-md-3'>
            <label className='form-label'>{t('Item Type')}</label>
            <Input value={item.item_type} onChange={(e) => updateItem(idx, 'item_type', e.target.value)} />
          </div>
          <div className='col-md-5'>
            <label className='form-label'>{t('Description')}</label>
            <Input value={item.description} onChange={(e) => updateItem(idx, 'description', e.target.value)} />
          </div>
          <div className='col-md-1'>
            <label className='form-label'>{t('Qty')}</label>
            <InputNumber
              min={1}
              style={{width: '100%'}}
              value={item.default_quantity}
              onChange={(v) => updateItem(idx, 'default_quantity', v)}
            />
          </div>
          <div className='col-md-2'>
            <label className='form-label'>{t('Notional Price')}</label>
            <InputNumber
              min={0}
              style={{width: '100%'}}
              value={item.notional_unit_price as any}
              onChange={(v) => updateItem(idx, 'notional_unit_price', v)}
            />
          </div>
          <div className='col-md-1 text-end'>
            <Popconfirm title={t('Remove this inclusion?')} onConfirm={() => removeItem(idx)}>
              <Button danger icon={<DeleteOutlined />} />
            </Popconfirm>
          </div>
        </div>
      ))}
    </div>
  )
}
export default React.memo(BillingPackageView)

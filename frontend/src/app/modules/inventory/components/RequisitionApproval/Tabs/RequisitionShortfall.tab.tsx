import React, {FC, useEffect, useState} from 'react'
import {Alert, Button, Empty, InputNumber, Skeleton, Table} from 'antd'
import {RequisitionApi, RequisitionApprovalApi} from 'src/app/api'
import SupplierSelect from 'src/app/components/Dropdown/SupplierSelect'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'

const RequisitionShortfallTab: FC<any> = (props) => {
  const {itemData} = props
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [shortfall, setShortfall] = useState<any[]>([])
  const [supplierId, setSupplierId] = useState<any>(null)
  const [creating, setCreating] = useState(false)
  const [createdPo, setCreatedPo] = useState<any>(null)

  useEffect(() => {
    if (!itemData?.id) return
    setLoading(true)
    RequisitionApprovalApi.shortfall(itemData.id)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setShortfall(
          (data.shortfall || []).map((row: any) => ({...row, unit_price: 0}))
        )
      })
      .catch(() => setShortfall([]))
      .finally(() => setLoading(false))
  }, [itemData?.id])

  const handlePriceChange = (itemId: any, value: any) => {
    setShortfall((prev) => prev.map((row) => (row.item_id === itemId ? {...row, unit_price: value || 0} : row)))
  }

  const handleCreatePo = () => {
    if (!supplierId) {
      Message.error('Please select a supplier')
      return
    }
    setCreating(true)
    RequisitionApi.convertToPurchaseOrder(itemData.id, {
      supplier_id: supplierId,
      items: shortfall.map((row) => ({
        item_id: row.item_id,
        quantity: row.shortfall_qty,
        unit_price: row.unit_price,
      })),
    })
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setCreatedPo(data)
        Message.success(`Purchase Order ${data.po_number} created`)
      })
      .catch(() => Message.error('Failed to create Purchase Order'))
      .finally(() => setCreating(false))
  }

  if (loading) return <Skeleton active paragraph={{rows: 3}} />

  if (itemData.linked_purchase_order_id || createdPo) {
    return (
      <Alert
        type='success'
        showIcon
        message={t('Purchase Order Created')}
        description={t(
          `A Purchase Order (#${createdPo?.purchase_order_id ?? itemData.linked_purchase_order_id}) has already been raised for this requisition's shortfall.`
        )}
      />
    )
  }

  if (!shortfall.length) {
    return <Empty description={t('No shortfall — warehouse stock can fully cover this requisition')} />
  }

  const columns = [
    {title: t('Item Code'), dataIndex: 'item_code', key: 'item_code'},
    {title: t('Item Name'), dataIndex: 'item_name', key: 'item_name'},
    {title: t('Requested Qty'), dataIndex: 'requested_qty', key: 'requested_qty'},
    {title: t('Available Qty'), dataIndex: 'available_qty', key: 'available_qty'},
    {title: t('Shortfall Qty'), dataIndex: 'shortfall_qty', key: 'shortfall_qty'},
    {
      title: t('Unit Price'),
      key: 'unit_price',
      render: (_: any, row: any) => (
        <InputNumber
          min={0}
          value={row.unit_price}
          onChange={(value) => handlePriceChange(row.item_id, value)}
        />
      ),
    },
  ]

  return (
    <div>
      <Alert
        type='warning'
        showIcon
        className='mb-4'
        message={t('Insufficient Warehouse Stock')}
        description={t(
          'Some items cannot be fully disbursed from warehouse stock. Raise a Purchase Order for the shortfall below.'
        )}
      />
      <Table rowKey='item_id' size='small' columns={columns} dataSource={shortfall} pagination={false} className='mb-4' />
      <div className='d-flex align-items-center gap-3'>
        <SupplierSelect
          supplierId={supplierId}
          placeholder={t('Select Supplier')}
          onSelect={(value: any) => setSupplierId(value)}
          style={{width: 260}}
        />
        <Button type='primary' onClick={handleCreatePo} loading={creating}>
          {t('Create Purchase Order for Shortfall')}
        </Button>
      </div>
    </div>
  )
}

export default React.memo(RequisitionShortfallTab)

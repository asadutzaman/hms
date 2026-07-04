import React, {useEffect} from 'react'
import {Empty, Select, Spin} from 'antd'
import {SelectProps} from 'antd/lib/select'
import {usePurchaseOrderList} from 'src/app/hooks/lists/usePurchaseOrderList'

interface Props extends SelectProps {
  purchaseOrderId: any
  placeholder?: string

  onLoad?: (value: any) => void
  onChange?: (value: any, option: any) => void
  onSelect?: (value: any, option: any) => void
}

const PurchaseOrderSelect: React.FC<Props> = (props) => {
  const {Option} = Select
  const {purchaseOrderId} = props

  const {purchaseOrderList, loadingPurchaseOrderList} = usePurchaseOrderList()

  useEffect(() => {
    if (purchaseOrderId && purchaseOrderList.length) {
      if (props.onLoad) {
        props.onLoad(purchaseOrderId)
      }
    }
  }, [purchaseOrderId, purchaseOrderList, props])

  const handleOnChanged = (value: any, option: any) => {
    if (props.onChange) {
      props.onChange(value, option)
    }
  }

  const handleOnSelect = (value: any, option: any) => {
    if (props.onSelect) {
      props.onSelect(value, option)
    }
  }

  return (
    <Select
      {...props}
      allowClear={true}
      showSearch
      placeholder={props.placeholder || '-- Select --'}
      value={purchaseOrderId}
      notFoundContent={loadingPurchaseOrderList ? <Spin size='small' /> : <Empty />}
      onChange={(value, option) => handleOnChanged(value, option)}
      onSelect={(value, option) => handleOnSelect(value, option)}
      loading={loadingPurchaseOrderList}
      optionFilterProp='children'
      filterOption={(input, option: any) =>
        option?.children?.toLowerCase()?.indexOf(input.toLowerCase()) >= 0
      }
    >
      {purchaseOrderList &&
        purchaseOrderList
          .filter((item: any) => ['approved', 'partially_received'].includes(item.po_status))
          .map((item: any, index: any) => {
            return (
              <Option key={`purchase-order-${index}`} value={item.id}>
                {item.po_number}
              </Option>
            )
          })}
    </Select>
  )
}

export default PurchaseOrderSelect

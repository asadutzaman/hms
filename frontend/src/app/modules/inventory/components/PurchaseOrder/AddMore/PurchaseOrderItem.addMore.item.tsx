import React, {FC} from 'react'
import {KTIcon} from 'src/_metronic/helpers'
import {Button, Form, InputNumber} from 'antd'

const PurchaseOrderItemAddMoreItem: FC<any> = (props) => {
  const {addMoreItemIndex, addMoreItem, handleAddMoreItemEdit, handleAddMoreItemDelete} = props

  return (
    <tr>
      <td>{addMoreItemIndex + 1}</td>
      <td>{addMoreItem.name}</td>
      <td>
        <Form.Item className='mb-0'>
          <InputNumber
            min={0}
            value={addMoreItem.quantity}
            onChange={(value) => handleAddMoreItemEdit('quantity', value, addMoreItemIndex)}
            style={{width: '100%'}}
          />
        </Form.Item>
      </td>
      <td>
        <Form.Item className='mb-0'>
          <InputNumber
            min={0}
            value={addMoreItem.unit_price}
            onChange={(value) => handleAddMoreItemEdit('unit_price', value, addMoreItemIndex)}
            style={{width: '100%'}}
          />
        </Form.Item>
      </td>
      <td>{((addMoreItem.quantity || 0) * (addMoreItem.unit_price || 0)).toFixed(2)}</td>
      <td>
        <Button
          className='btn btn-icon btn-bg-light btn-active-color-danger btn-sm me-1 mt-1'
          onClick={() => handleAddMoreItemDelete(addMoreItemIndex)}
        >
          <KTIcon iconName='trash' className='fs-3' />
        </Button>
      </td>
    </tr>
  )
}

export default React.memo(PurchaseOrderItemAddMoreItem)

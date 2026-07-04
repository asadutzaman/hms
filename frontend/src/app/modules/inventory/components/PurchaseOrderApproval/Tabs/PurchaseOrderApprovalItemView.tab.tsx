import React, {FC} from 'react'
import {useLang} from 'src/app/hooks/useLang'

const PurchaseOrderApprovalItemViewTab: FC<any> = (props) => {
  const {itemData} = props
  const {t} = useLang()

  return (
    <div className='table-responsive'>
      <table className='table table-bordered align-middle gs-1 gy-1'>
        <thead>
          <tr>
            <th>{t('SL.')}</th>
            <th>{t('Item')}</th>
            <th>{t('Item Code')}</th>
            <th>{t('Quantity')}</th>
            <th>{t('Unit Price')}</th>
            <th>{t('Line Total')}</th>
            <th>{t('Received Quantity')}</th>
          </tr>
        </thead>

        <tbody>
          {itemData.purchase_order_items_list_data !== undefined &&
            itemData.purchase_order_items_list_data.map((localItem: any, localIndex: any) => (
              <tr key={`local-${localIndex}`}>
                <td>{++localIndex}</td>
                <td>{localItem.item_info.name_en ?? localItem.item_info.name_bn}</td>
                <td>{localItem.item_info.code}</td>
                <td>{localItem.quantity}</td>
                <td>{localItem.unit_price}</td>
                <td>{localItem.line_total}</td>
                <td>{localItem.received_quantity}</td>
              </tr>
            ))}
        </tbody>
      </table>
    </div>
  )
}
export default React.memo(PurchaseOrderApprovalItemViewTab)

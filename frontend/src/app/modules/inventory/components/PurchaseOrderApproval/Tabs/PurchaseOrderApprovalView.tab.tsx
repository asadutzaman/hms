import React, {FC} from 'react'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'
import {StatusEnum} from 'src/app/utils/enums'

const PurchaseOrderApprovalViewTab: FC<any> = (props) => {
  const {itemData} = props
  const {t} = useLang()

  return (
    <div className='table-responsive'>
      <table className='table table-row-dashed table-row-gray-300 align-middle gs-0 gy-1'>
        <tr>
          <td width={'20%'}>{t('PO Number')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{itemData.po_number}</td>
        </tr>
        <tr>
          <td width={'20%'}>{t('Supplier')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{itemData.supplier_name}</td>
        </tr>
        <tr>
          <td width={'20%'}>{t('Branch')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{itemData.branch_name}</td>
        </tr>
        <tr>
          <td width={'20%'}>{t('Order Date')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{itemData.order_date}</td>
        </tr>
        <tr>
          <td width={'20%'}>{t('Expected Delivery Date')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{itemData.expected_delivery_date}</td>
        </tr>
        <tr>
          <td width={'20%'}>{t('PO Status')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>
            <span className='text-capitalize'>{itemData.po_status}</span>
          </td>
        </tr>
        <tr>
          <td width={'20%'}>{t('Approval Status')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{itemData.process_status}</td>
        </tr>
        <tr>
          <td width={'20%'}>{t('Status')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{StatusEnum[itemData.status]}</td>
        </tr>
        <tr>
          <td width={'20%'}>{t('Created Time')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{DateTimeUtils.formatDateTimeA(itemData.created_at)}</td>
        </tr>
        <tr>
          <td width={'20%'}>{t('Created By')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{itemData.created_by_name}</td>
        </tr>
      </table>
    </div>
  )
}
export default React.memo(PurchaseOrderApprovalViewTab)

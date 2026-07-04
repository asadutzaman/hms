import React, {FC} from 'react'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {PurchaseOrderAction} from '../Actions/PurchaseOrder.actions'
import ViewTabList from 'src/app/components/Tab/ViewTabList'
import PurchaseOrderViewTab from '../Tabs/PurchaseOrderView.tab'
import PurchaseOrderItemViewTab from '../Tabs/PurchaseOrderItemView.tab'
import {useLang} from 'src/app/hooks/useLang'

const PurchaseOrderView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc, loading, ...restProps} = props
  const {t} = useLang()

  const viewTabListData: any = [
    {
      tabIndex: 1,
      label: t('Purchase Order Info'),
      permission: '',
      component: <PurchaseOrderViewTab itemData={itemData} />,
    },
    {
      tabIndex: 2,
      label: t('Purchase Order Items'),
      permission: '',
      component: <PurchaseOrderItemViewTab itemData={itemData} {...restProps} />,
    },
  ]
  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction
            entityId={itemData.id}
            actionItem={PurchaseOrderAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={PurchaseOrderAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>
      {loading === false && <ViewTabList activeTabIndex={'1'} viewTabListData={viewTabListData} />}
    </div>
  )
}
export default React.memo(PurchaseOrderView)

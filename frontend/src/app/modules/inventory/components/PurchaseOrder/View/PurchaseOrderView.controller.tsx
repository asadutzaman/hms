import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {PurchaseOrderApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import PurchaseOrderView from './PurchaseOrderView.view'

const initialState = {
  modalTitle: 'Purchase Order Info',
  itemData: {},
  loading: false,
  fields: {},
  message: {
    network_error: 'A network error occurred. Please try again later.',
  },
}

const PurchaseOrderViewController: FC<any> = (props) => {
  const {
    BaseCrudViewService,
    modalTitle,
    itemData,
    setItemData,
    loading,
    entityId,
    reloadView,
    isShowView,
    handleCallbackFunc,
  } = useCrudViewService(PurchaseOrderApi, initialState, props)

  useEffect(() => {
    setItemData(initialState.itemData)
    if (entityId && isShowView) {
      loadData()
    }
  }, [entityId, reloadView])

  const loadData = (): Promise<any> => {
    return BaseCrudViewService.loadData()
  }

  return (
    <DrawerView
      loading={loading}
      reloadView={reloadView}
      isShowView={isShowView}
      modalTitle={modalTitle}
      itemData={itemData}
      component={PurchaseOrderView}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(PurchaseOrderViewController)

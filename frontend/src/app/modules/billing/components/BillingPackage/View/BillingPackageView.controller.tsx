import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {BillingPackageApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import BillingPackageView from './BillingPackageView.view'

const initialState = {
  modalTitle: 'Billing Package Info',
  itemData: {},
  loading: false,
  fields: {},
  message: {
    network_error: 'A network error occurred. Please try again later.',
  },
}

const BillingPackageViewController: FC<any> = (props) => {
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
  } = useCrudViewService(BillingPackageApi, initialState, props)

  useEffect(() => {
    setItemData(initialState.itemData)
    if (entityId && isShowView) {
      loadData()
    }
  }, [entityId, reloadView])

  const loadData = (): Promise<any> => BaseCrudViewService.loadData()

  return (
    <DrawerView
      drawerWidth='60%'
      loading={loading}
      reloadView={reloadView}
      isShowView={isShowView}
      modalTitle={modalTitle}
      itemData={itemData}
      component={BillingPackageView}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(BillingPackageViewController)

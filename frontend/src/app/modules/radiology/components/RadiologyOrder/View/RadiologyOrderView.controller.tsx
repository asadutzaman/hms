import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {RadiologyOrderApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import RadiologyOrderView from './RadiologyOrderView.view'

const initialState = {
  modalTitle: 'Radiology Order Detail',
  itemData: {},
  loading: false,
  fields: {},
  message: {
    network_error: 'A network error occurred. Please try again later.',
  },
}

const RadiologyOrderViewController: FC<any> = (props) => {
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
  } = useCrudViewService(RadiologyOrderApi, initialState, props)

  useEffect(() => {
    setItemData(initialState.itemData)
    if (entityId && isShowView) {
      loadData()
    }
  }, [entityId, isShowView, reloadView])

  const loadData = (): Promise<any> => BaseCrudViewService.loadData()

  return (
    <DrawerView
      drawerWidth='75%'
      loading={loading}
      reloadView={reloadView}
      isShowView={isShowView}
      modalTitle={modalTitle}
      itemData={itemData}
      component={RadiologyOrderView}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(RadiologyOrderViewController)

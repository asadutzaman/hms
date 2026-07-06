import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {ErVisitApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import ErVisitView from './ErVisitView.view'

const initialState = {
  modalTitle: 'ER Visit Info',
  itemData: {},
  loading: false,
  fields: {},
  message: {network_error: 'A network error occurred. Please try again later.'},
}

const ErVisitViewController: FC<any> = (props) => {
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
  } = useCrudViewService(ErVisitApi, initialState, props)

  useEffect(() => {
    setItemData(initialState.itemData)
    if (entityId && isShowView) {
      loadData()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
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
      component={ErVisitView}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(ErVisitViewController)

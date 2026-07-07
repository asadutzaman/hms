import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {RadiologyTestApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import RadiologyTestView from './RadiologyTestView.view'

const initialState = {
  modalTitle: 'Radiology Test Info',
  itemData: {},
  loading: false,
  fields: {},
  message: {
    network_error: 'A network error occurred. Please try again later.',
  },
}

const RadiologyTestViewController: FC<any> = (props) => {
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
  } = useCrudViewService(RadiologyTestApi, initialState, props)

  useEffect(() => {
    setItemData(initialState.itemData)
    if (entityId && isShowView) {
      loadData()
    }
  }, [entityId, reloadView])

  const loadData = (): Promise<any> => BaseCrudViewService.loadData()

  return (
    <DrawerView
      loading={loading}
      reloadView={reloadView}
      isShowView={isShowView}
      modalTitle={modalTitle}
      itemData={itemData}
      component={RadiologyTestView}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(RadiologyTestViewController)

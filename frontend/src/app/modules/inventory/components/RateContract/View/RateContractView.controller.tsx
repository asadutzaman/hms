import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {RateContractApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import RateContractView from './RateContractView.view'

const initialState = {
  modalTitle: 'Rate Contract Info',
  itemData: {},
  loading: false,
  fields: {},
  message: {
    network_error: 'A network error occurred. Please try again later.',
  },
}

const RateContractViewController: FC<any> = (props) => {
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
  } = useCrudViewService(RateContractApi, initialState, props)

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
      drawerWidth='55%'
      loading={loading}
      reloadView={reloadView}
      isShowView={isShowView}
      modalTitle={modalTitle}
      itemData={itemData}
      component={RateContractView}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(RateContractViewController)

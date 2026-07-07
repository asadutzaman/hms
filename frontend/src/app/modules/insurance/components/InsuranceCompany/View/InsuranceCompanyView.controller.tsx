import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {InsuranceCompanyApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import InsuranceCompanyView from './InsuranceCompanyView.view'

const initialState = {
  modalTitle: 'Insurance Company Info',
  itemData: {},
  loading: false,
  fields: {},
  message: {
    network_error: 'A network error occurred. Please try again later.',
  },
}

const InsuranceCompanyViewController: FC<any> = (props) => {
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
  } = useCrudViewService(InsuranceCompanyApi, initialState, props)

  useEffect(() => {
    setItemData(initialState.itemData)
    if (entityId && isShowView) {
      loadData()
    }
  }, [entityId, reloadView])

  const loadData = (): Promise<any> => BaseCrudViewService.loadData()

  return (
    <DrawerView
      drawerWidth='65%'
      loading={loading}
      reloadView={reloadView}
      isShowView={isShowView}
      modalTitle={modalTitle}
      itemData={itemData}
      component={InsuranceCompanyView}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(InsuranceCompanyViewController)

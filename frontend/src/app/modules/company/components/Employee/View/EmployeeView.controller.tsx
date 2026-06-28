import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {EmployeeApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import EmployeeView from './EmployeeView.view'

const initialState = {
  modalTitle: 'Employee Info',
  itemData: {},
  loading: false,
  fields: {},
  message: {
    network_error: 'A network error occurred. Please try again later.',
  },
}

const EmployeeViewController: FC<any> = (props) => {
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
  } = useCrudViewService(EmployeeApi, initialState, props)

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
      drawerWidth='60%'
      loading={loading}
      reloadView={reloadView}
      isShowView={isShowView}
      modalTitle={modalTitle}
      itemData={itemData}
      component={EmployeeView}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(EmployeeViewController)

import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {DoctorScheduleApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import DoctorScheduleViewBody from './DoctorScheduleView.view'

const initialState = {
  modalTitle: 'Doctor Schedule Details',
  itemData: {},
  loading: false,
  fields: {},
  message: {
    network_error: 'A network error occurred. Please try again later.',
  },
}

const DoctorScheduleViewController: FC<any> = (props) => {
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
  } = useCrudViewService(DoctorScheduleApi, initialState, props)

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
      drawerWidth='75%'
      loading={loading}
      reloadView={reloadView}
      isShowView={isShowView}
      modalTitle={modalTitle}
      itemData={itemData}
      component={DoctorScheduleViewBody}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(DoctorScheduleViewController)

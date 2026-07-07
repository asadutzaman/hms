import React, {FC, useEffect} from 'react'
import {useCrudViewService} from 'src/app/hooks/crud/useCrudViewService'
import {RadiologyReportTemplateApi} from 'src/app/api'
import DrawerView from 'src/app/components/Drawer/DrawerView'
import RadiologyReportTemplateView from './RadiologyReportTemplateView.view'

const initialState = {
  modalTitle: 'Report Template Info',
  itemData: {},
  loading: false,
  fields: {},
  message: {
    network_error: 'A network error occurred. Please try again later.',
  },
}

const RadiologyReportTemplateViewController: FC<any> = (props) => {
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
  } = useCrudViewService(RadiologyReportTemplateApi, initialState, props)

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
      component={RadiologyReportTemplateView}
      handleCallbackFunc={handleCallbackFunc}
    />
  )
}

export default React.memo(RadiologyReportTemplateViewController)

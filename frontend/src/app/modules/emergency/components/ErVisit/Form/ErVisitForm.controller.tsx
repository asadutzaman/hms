import React, {FC, useEffect} from 'react'
import {ErVisitApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import ErVisitAddOrEditForm from './ErVisitForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Register Emergency Patient',
  itemData: {},
  fields: {
    patient_id: null,
    arrival_mode: 'walk_in',
    chief_complaint: null,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'Patient registered successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const ErVisitFormController: FC<any> = (props) => {
  const {
    BaseCrudFormService,
    entityId,
    modalTitle,
    setModalTitle,
    isNewRecord,
    setIsNewRecord,
    isShowForm,
    reloadForm,
    itemData,
    loading,
    resetForm,
    isSubmitting,
    formRef,
    initialValues,
    handleChange,
    handleSubmitFailed,
    handleCallbackFunc,
  } = useCrudFormService(ErVisitApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit ER Visit')
      resetForm()
      loadData()
    } else {
      resetForm()
      setModalTitle(initialState.modalTitle)
      setIsNewRecord(initialState.isNewRecord)
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [entityId, reloadForm])

  const loadData = (): void => {
    BaseCrudFormService.loadData().then((res: any) => {
      const initFormDta = {
        arrival_mode: res.data.arrival_mode,
        chief_complaint: res.data.chief_complaint,
      }
      handleChange(initFormDta)
      formRef.setFieldsValue(initFormDta)
    })
  }

  const handleSubmit = async (values: any): Promise<void> => {
    try {
      if (entityId) {
        await BaseCrudFormService.handleUpdate(values)
      } else {
        await BaseCrudFormService.handleCreate(values)
      }
    } catch (error: any) {
      handleSubmitFailed(error)
    }
  }

  return (
    <div className='form-page-container'>
      <DrawerForm
        drawerWidth='45%'
        loading={loading}
        isNewRecord={isNewRecord}
        itemData={itemData}
        modalTitle={modalTitle}
        isSubmitting={isSubmitting}
        isShowForm={isShowForm}
        formRef={formRef}
        initialValues={initialValues}
        component={ErVisitAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(ErVisitFormController)

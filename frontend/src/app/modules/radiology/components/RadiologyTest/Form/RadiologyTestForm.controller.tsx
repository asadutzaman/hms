import React, {FC, useEffect} from 'react'
import {RadiologyTestApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import RadiologyTestAddOrEditForm from './RadiologyTestForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create Radiology Test',
  itemData: {},
  fields: {
    code: null,
    name: null,
    modality: null,
    body_part: null,
    tat_hours: null,
    default_price: null,
    description: null,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'The operation performed successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const RadiologyTestFormController: FC<any> = (props) => {
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
  } = useCrudFormService(RadiologyTestApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Radiology Test')
      resetForm()
      loadData()
    } else {
      resetForm()
      setModalTitle(initialState.modalTitle)
      setIsNewRecord(initialState.isNewRecord)
    }
  }, [entityId, reloadForm])

  const loadData = (): void => {
    BaseCrudFormService.loadData().then((res: any) => {
      const initFormData = {
        code: res.data.code,
        name: res.data.name,
        modality: res.data.modality,
        body_part: res.data.body_part,
        tat_hours: res.data.tat_hours,
        default_price: res.data.default_price,
        description: res.data.description,
      }
      handleChange(initFormData)
      formRef.setFieldsValue(initFormData)
    })
  }

  const handleSubmit = async (values: any): Promise<void> => {
    try {
      if (entityId) {
        await handleUpdate(values)
      } else {
        await handleCreate(values)
      }
    } catch (error: any) {
      handleSubmitFailed(error)
    }
  }

  const handleCreate = (values: any): Promise<any> => BaseCrudFormService.handleCreate({...values})
  const handleUpdate = (values: any): Promise<any> => BaseCrudFormService.handleUpdate({...values})

  return (
    <div className='form-page-container'>
      <DrawerForm
        loading={loading}
        isNewRecord={isNewRecord}
        itemData={itemData}
        modalTitle={modalTitle}
        isSubmitting={isSubmitting}
        isShowForm={isShowForm}
        formRef={formRef}
        initialValues={initialValues}
        component={RadiologyTestAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(RadiologyTestFormController)

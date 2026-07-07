import React, {FC, useEffect} from 'react'
import {NotificationTemplateApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import NotificationTemplateAddOrEditForm from './NotificationTemplateForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create Notification Template',
  itemData: {},
  fields: {
    key: null,
    name: null,
    channel: null,
    subject_template: null,
    body_template: null,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'The operation performed successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const NotificationTemplateFormController: FC<any> = (props) => {
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
  } = useCrudFormService(NotificationTemplateApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Notification Template')
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
        key: res.data.key,
        name: res.data.name,
        channel: res.data.channel,
        subject_template: res.data.subject_template,
        body_template: res.data.body_template,
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
        component={NotificationTemplateAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(NotificationTemplateFormController)

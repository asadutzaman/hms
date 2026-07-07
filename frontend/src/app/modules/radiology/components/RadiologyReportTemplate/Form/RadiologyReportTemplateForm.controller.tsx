import React, {FC, useEffect} from 'react'
import {RadiologyReportTemplateApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import RadiologyReportTemplateAddOrEditForm from './RadiologyReportTemplateForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create Report Template',
  itemData: {},
  fields: {
    name: null,
    modality: null,
    body_part: null,
    findings_template: null,
    impression_template: null,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'The operation performed successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const RadiologyReportTemplateFormController: FC<any> = (props) => {
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
  } = useCrudFormService(RadiologyReportTemplateApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Report Template')
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
        name: res.data.name,
        modality: res.data.modality,
        body_part: res.data.body_part,
        findings_template: res.data.findings_template,
        impression_template: res.data.impression_template,
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
        component={RadiologyReportTemplateAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(RadiologyReportTemplateFormController)

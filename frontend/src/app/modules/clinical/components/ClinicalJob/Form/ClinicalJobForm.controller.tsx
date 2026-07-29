import React, {FC, useEffect} from 'react'
import {ClinicalJobApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import ClinicalJobAddOrEditForm from './ClinicalJobForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create Clinical Task',
  itemData: {},
  fields: {
    title: null,
    job_type: null,
    priority: null,
    patient_id: null,
    ward_id: null,
    role_type: null,
    state: null,
    due_at: null,
    description: null,
    status: 1,
  },
  isNewRecord: true, loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'The operation performed successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const ClinicalJobFormController: FC<any> = (props) => {
  const {
    BaseCrudFormService, entityId, modalTitle, setModalTitle, isNewRecord, setIsNewRecord,
    isShowForm, reloadForm, itemData, loading, resetForm, isSubmitting, formRef, initialValues,
    handleChange, handleSubmitFailed, handleCallbackFunc,
  } = useCrudFormService(ClinicalJobApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false); setModalTitle('Edit Clinical Task'); resetForm(); loadData()
    } else {
      resetForm(); setModalTitle(initialState.modalTitle); setIsNewRecord(initialState.isNewRecord)
    }
  }, [entityId, reloadForm])

  const loadData = (): void => {
    BaseCrudFormService.loadData().then((res: any) => {
      const initFormDta = {
        title: res.data.title,
        job_type: res.data.job_type,
        priority: res.data.priority,
        patient_id: res.data.patient_id,
        ward_id: res.data.ward_id,
        role_type: res.data.role_type,
        state: res.data.state,
        due_at: res.data.due_at,
        description: res.data.description,
        status: res.data.status,
      }
      handleChange(initFormDta)
      formRef.setFieldsValue(initFormDta)
    })
  }
  const handleSubmit = (values: any): void => { entityId ? handleUpdate(values) : handleCreate(values) }
  const handleCreate = (values: any): Promise<any> => BaseCrudFormService.handleCreate({...values})
  const handleUpdate = (values: any): Promise<any> => BaseCrudFormService.handleUpdate({...values})

  return (
    <div className='form-page-container'>
      <DrawerForm loading={loading} isNewRecord={isNewRecord} itemData={itemData} modalTitle={modalTitle}
        isSubmitting={isSubmitting} isShowForm={isShowForm} formRef={formRef} initialValues={initialValues}
        component={ClinicalJobAddOrEditForm} handleChange={handleChange} handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed} handleCallbackFunc={handleCallbackFunc} />
    </div>
  )
}
export default React.memo(ClinicalJobFormController)

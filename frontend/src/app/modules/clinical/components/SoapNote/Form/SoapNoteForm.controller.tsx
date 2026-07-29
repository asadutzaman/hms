import React, {FC, useEffect} from 'react'
import {SoapNoteApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import SoapNoteAddOrEditForm from './SoapNoteForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create SOAP Note',
  itemData: {},
  fields: {
    patient_id: null,
    subjective: null,
    objective: null,
    assessment: null,
    plan: null,
    status: 1,
  },
  isNewRecord: true, loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'The operation performed successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const SoapNoteFormController: FC<any> = (props) => {
  const {
    BaseCrudFormService, entityId, modalTitle, setModalTitle, isNewRecord, setIsNewRecord,
    isShowForm, reloadForm, itemData, loading, resetForm, isSubmitting, formRef, initialValues,
    handleChange, handleSubmitFailed, handleCallbackFunc,
  } = useCrudFormService(SoapNoteApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false); setModalTitle('Edit SOAP Note'); resetForm(); loadData()
    } else {
      resetForm(); setModalTitle(initialState.modalTitle); setIsNewRecord(initialState.isNewRecord)
    }
  }, [entityId, reloadForm])

  const loadData = (): void => {
    BaseCrudFormService.loadData().then((res: any) => {
      const initFormDta = {
        patient_id: res.data.patient_id,
        subjective: res.data.subjective,
        objective: res.data.objective,
        assessment: res.data.assessment,
        plan: res.data.plan,
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
        component={SoapNoteAddOrEditForm} handleChange={handleChange} handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed} handleCallbackFunc={handleCallbackFunc} />
    </div>
  )
}
export default React.memo(SoapNoteFormController)

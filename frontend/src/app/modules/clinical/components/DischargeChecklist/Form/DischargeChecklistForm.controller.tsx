import React, {FC, useEffect} from 'react'
import {DischargeChecklistApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import DischargeChecklistAddOrEditForm from './DischargeChecklistForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create Discharge Checklist',
  itemData: {},
  fields: {
    ipd_admission_id: null,
    state: null,
    status: 1,
  },
  isNewRecord: true, loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'The operation performed successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const DischargeChecklistFormController: FC<any> = (props) => {
  const {
    BaseCrudFormService, entityId, modalTitle, setModalTitle, isNewRecord, setIsNewRecord,
    isShowForm, reloadForm, itemData, loading, resetForm, isSubmitting, formRef, initialValues,
    handleChange, handleSubmitFailed, handleCallbackFunc,
  } = useCrudFormService(DischargeChecklistApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false); setModalTitle('Edit Discharge Checklist'); resetForm(); loadData()
    } else {
      resetForm(); setModalTitle(initialState.modalTitle); setIsNewRecord(initialState.isNewRecord)
    }
  }, [entityId, reloadForm])

  const loadData = (): void => {
    BaseCrudFormService.loadData().then((res: any) => {
      const initFormDta = {
        ipd_admission_id: res.data.ipd_admission_id,
        state: res.data.state,
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
        component={DischargeChecklistAddOrEditForm} handleChange={handleChange} handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed} handleCallbackFunc={handleCallbackFunc} />
    </div>
  )
}
export default React.memo(DischargeChecklistFormController)

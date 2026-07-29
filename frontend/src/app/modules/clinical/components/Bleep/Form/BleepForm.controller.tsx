import React, {FC, useEffect} from 'react'
import {BleepApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import BleepAddOrEditForm from './BleepForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create Bleep',
  itemData: {},
  fields: {
    message: null,
    priority: null,
    callback: null,
    patient_id: null,
    ward_id: null,
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

const BleepFormController: FC<any> = (props) => {
  const {
    BaseCrudFormService, entityId, modalTitle, setModalTitle, isNewRecord, setIsNewRecord,
    isShowForm, reloadForm, itemData, loading, resetForm, isSubmitting, formRef, initialValues,
    handleChange, handleSubmitFailed, handleCallbackFunc,
  } = useCrudFormService(BleepApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false); setModalTitle('Edit Bleep'); resetForm(); loadData()
    } else {
      resetForm(); setModalTitle(initialState.modalTitle); setIsNewRecord(initialState.isNewRecord)
    }
  }, [entityId, reloadForm])

  const loadData = (): void => {
    BaseCrudFormService.loadData().then((res: any) => {
      const initFormDta = {
        message: res.data.message,
        priority: res.data.priority,
        callback: res.data.callback,
        patient_id: res.data.patient_id,
        ward_id: res.data.ward_id,
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
        component={BleepAddOrEditForm} handleChange={handleChange} handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed} handleCallbackFunc={handleCallbackFunc} />
    </div>
  )
}
export default React.memo(BleepFormController)

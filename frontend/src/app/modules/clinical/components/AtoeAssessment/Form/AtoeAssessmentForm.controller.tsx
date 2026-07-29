import React, {FC, useEffect} from 'react'
import {AtoeAssessmentApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import AtoeAssessmentAddOrEditForm from './AtoeAssessmentForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create A-to-E Assessment',
  itemData: {},
  fields: {
    patient_id: null,
    news2_score: null,
    airway: null,
    breathing: null,
    circulation: null,
    disability: null,
    exposure: null,
    impression: null,
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

const AtoeAssessmentFormController: FC<any> = (props) => {
  const {
    BaseCrudFormService, entityId, modalTitle, setModalTitle, isNewRecord, setIsNewRecord,
    isShowForm, reloadForm, itemData, loading, resetForm, isSubmitting, formRef, initialValues,
    handleChange, handleSubmitFailed, handleCallbackFunc,
  } = useCrudFormService(AtoeAssessmentApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false); setModalTitle('Edit A-to-E Assessment'); resetForm(); loadData()
    } else {
      resetForm(); setModalTitle(initialState.modalTitle); setIsNewRecord(initialState.isNewRecord)
    }
  }, [entityId, reloadForm])

  const loadData = (): void => {
    BaseCrudFormService.loadData().then((res: any) => {
      const initFormDta = {
        patient_id: res.data.patient_id,
        news2_score: res.data.news2_score,
        airway: res.data.airway,
        breathing: res.data.breathing,
        circulation: res.data.circulation,
        disability: res.data.disability,
        exposure: res.data.exposure,
        impression: res.data.impression,
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
        component={AtoeAssessmentAddOrEditForm} handleChange={handleChange} handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed} handleCallbackFunc={handleCallbackFunc} />
    </div>
  )
}
export default React.memo(AtoeAssessmentFormController)

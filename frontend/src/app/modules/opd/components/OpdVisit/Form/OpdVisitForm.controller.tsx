import React, {FC, useEffect} from 'react'
import {OpdVisitApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import OpdVisitAddOrEditForm from './OpdVisitForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'New OPD Visit',
  itemData: {},
  fields: {
    patient_id: null,
    doctor_id: null,
    department_id: null,
    appointment_id: null,
    visit_type: 'walk_in',
    visit_date: null,
    token_number: null,
    chief_complaint: null,
    history: null,
    examination: null,
    clinical_notes: null,
    advice: null,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'OPD visit created successfully.',
    update_success: 'OPD visit updated successfully.',
  },
}

const OpdVisitFormController: FC<any> = (props) => {
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
  } = useCrudFormService(OpdVisitApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit OPD Visit')
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
      const d = res.data
      const initFormData = {
        patient_id: d.patient_id,
        doctor_id: d.doctor_id,
        department_id: d.department_id,
        appointment_id: d.appointment_id,
        visit_type: d.visit_type,
        visit_date: d.visit_date,
        token_number: d.token_number,
        chief_complaint: d.chief_complaint,
        history: d.history,
        examination: d.examination,
        clinical_notes: d.clinical_notes,
        advice: d.advice,
      }
      handleChange(initFormData)
      formRef.setFieldsValue(initFormData)
    })
  }

  const handleSubmit = (values: any): void => {
    if (entityId) {
      handleUpdate(values)
    } else {
      handleCreate(values)
    }
  }

  const handleCreate = (values: any): Promise<any> =>
    BaseCrudFormService.handleCreate({...values})

  const handleUpdate = (values: any): Promise<any> =>
    BaseCrudFormService.handleUpdate({...values})

  return (
    <div className='form-page-container form-page-container-opd-visit'>
      <DrawerForm
        drawerWidth='80%'
        loading={loading}
        isNewRecord={isNewRecord}
        itemData={itemData}
        modalTitle={modalTitle}
        isSubmitting={isSubmitting}
        isShowForm={isShowForm}
        formRef={formRef}
        initialValues={initialValues}
        component={OpdVisitAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(OpdVisitFormController)

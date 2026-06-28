import React, {FC, useEffect} from 'react'
import {AppointmentApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import AppointmentAddOrEditForm from './AppointmentForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Book Appointment',
  itemData: {},
  fields: {
    patient_id: null,
    // Inline patient fields (when booking for a new patient)
    new_patient_first_name: null,
    new_patient_last_name: null,
    new_patient_primary_phone: null,
    new_patient_date_of_birth: null,
    new_patient_gender: null,
    // Schedule fields
    doctor_id: null,
    department_id: null,
    chamber_id: null,
    schedule_id: null,
    appointment_slot_id: null,
    appointment_date: null,
    start_time: null,
    end_time: null,
    duration_minutes: null,
    token_number: null,
    source: 'online',
    consultation_mode: 'in_person',
    consultation_fee: null,
    follow_up_fee: null,
    reason: null,
    symptoms: null,
    notes: null,
    internal_notes: null,
    referral_doctor_id: null,
    referral_notes: null,
    is_follow_up: false,
    parent_appointment_id: null,
    send_sms_reminder: true,
    send_email_reminder: false,
    status: 1,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'Appointment booked successfully.',
    update_success: 'Appointment updated successfully.',
  },
}

const AppointmentFormController: FC<any> = (props) => {
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
  } = useCrudFormService(AppointmentApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Appointment')
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
        chamber_id: d.chamber_id,
        schedule_id: d.schedule_id,
        appointment_slot_id: d.appointment_slot_id,
        appointment_date: d.appointment_date,
        start_time: d.start_time,
        end_time: d.end_time,
        duration_minutes: d.duration_minutes,
        token_number: d.token_number,
        source: d.source,
        consultation_mode: d.consultation_mode,
        consultation_fee: d.consultation_fee,
        follow_up_fee: d.follow_up_fee,
        reason: d.reason,
        symptoms: d.symptoms,
        notes: d.notes,
        internal_notes: d.internal_notes,
        referral_doctor_id: d.referral_doctor_id,
        referral_notes: d.referral_notes,
        is_follow_up: d.is_follow_up,
        parent_appointment_id: d.parent_appointment_id,
        send_sms_reminder: d.send_sms_reminder,
        send_email_reminder: d.send_email_reminder,
        status: d.status,
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
    <div className='form-page-container form-page-container-appointment'>
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
        component={AppointmentAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(AppointmentFormController)

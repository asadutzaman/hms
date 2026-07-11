import React, {FC, useEffect} from 'react'
import {useLocation} from 'react-router-dom'
import {DoctorScheduleApi} from 'src/app/api'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import DoctorScheduleForm from './DoctorScheduleForm.form'

const initialState = {
  modalTitle: 'Doctor Schedule',
  itemData: {},
  fields: {
    doctor_id: null,
    schedule_type: 'regular',
    effective_from: null,
    effective_to: null,
    day_of_week: null,
    start_time: null,
    end_time: null,
    slot_duration_minutes: 15,
    max_patients_per_slot: 1,
    consultation_mode: 'in_person',
    chamber_id: null,
    department_id: null,
    is_default: false,
    status: 1,
    slots: [],
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'Doctor schedule saved successfully.',
    update_success: 'Doctor schedule updated successfully.',
  },
}

const DoctorScheduleFormController: FC<any> = (props) => {
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
  } = useCrudFormService(DoctorScheduleApi, initialState, props)

  const location = useLocation()
  const isEditMode = location.pathname.includes('/edit/')

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Doctor Schedule')
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
      const d = res?.data || {}
      const initFormData = {
        doctor_id: d.doctor_id,
        schedule_type: d.schedule_type,
        effective_from: d.effective_from,
        effective_to: d.effective_to,
        day_of_week: d.day_of_week,
        start_time: d.start_time,
        end_time: d.end_time,
        slot_duration_minutes: d.slot_duration_minutes,
        max_patients_per_slot: d.max_patients_per_slot,
        consultation_mode: d.consultation_mode,
        chamber_id: d.chamber_id,
        department_id: d.department_id,
        is_default: d.is_default,
        status: d.status,
        slots: d.slots || [],
      }
      handleChange(initFormData)
      formRef.setFieldsValue(initFormData)
    })
  }

  const handleSubmit = (values: any): void => {
    if (entityId) {
      BaseCrudFormService.handleUpdate({...values})
    } else {
      BaseCrudFormService.handleCreate({...values})
    }
  }

  return (
    <div className='form-page-container form-page-container-doctor-schedule'>
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
        component={DoctorScheduleForm}
        isEditMode={isEditMode}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(DoctorScheduleFormController)
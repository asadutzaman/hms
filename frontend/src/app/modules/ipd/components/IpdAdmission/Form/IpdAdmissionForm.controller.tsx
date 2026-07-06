import React, {FC, useEffect} from 'react'
import dayjs from 'dayjs'
import {IpdAdmissionApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import IpdAdmissionAddOrEditForm from './IpdAdmissionForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Admit Patient',
  itemData: {},
  fields: {
    patient_id: null,
    ward_id: null,
    bed_id: null,
    admission_type: 'emergency',
    admission_date: dayjs(),
    expected_discharge_date: null,
    attending_doctor_id: null,
    department_id: null,
    diagnosis_at_admission: null,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'Patient admitted successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const IpdAdmissionFormController: FC<any> = (props) => {
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
  } = useCrudFormService(IpdAdmissionApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Admission')
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
      const initFormDta = {
        attending_doctor_id: res.data.attending_doctor_id,
        department_id: res.data.department_id,
        expected_discharge_date: res.data.expected_discharge_date ? dayjs(res.data.expected_discharge_date) : null,
        diagnosis_at_admission: res.data.diagnosis_at_admission,
      }
      handleChange(initFormDta)
      formRef.setFieldsValue(initFormDta)
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

  const handleCreate = (values: any): Promise<any> => {
    const payload = {
      ...values,
      admission_date: values.admission_date ? values.admission_date.format('YYYY-MM-DD HH:mm:ss') : undefined,
      expected_discharge_date: values.expected_discharge_date
        ? values.expected_discharge_date.format('YYYY-MM-DD')
        : undefined,
    }
    return BaseCrudFormService.handleCreate(payload)
  }

  const handleUpdate = (values: any): Promise<any> => {
    const payload = {
      attending_doctor_id: values.attending_doctor_id,
      department_id: values.department_id,
      expected_discharge_date: values.expected_discharge_date
        ? values.expected_discharge_date.format('YYYY-MM-DD')
        : undefined,
      diagnosis_at_admission: values.diagnosis_at_admission,
    }
    return BaseCrudFormService.handleUpdate(payload)
  }

  return (
    <div className='form-page-container'>
      <DrawerForm
        drawerWidth='55%'
        loading={loading}
        isNewRecord={isNewRecord}
        itemData={itemData}
        modalTitle={modalTitle}
        isSubmitting={isSubmitting}
        isShowForm={isShowForm}
        formRef={formRef}
        initialValues={initialValues}
        component={IpdAdmissionAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(IpdAdmissionFormController)

import React, {FC, useEffect} from 'react'
import {EmployeeApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import EmployeeAddOrEditForm from './EmployeeForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Add Employee',
  itemData: {},
  fields: {
    name_en: null,
    name_bn: null,
    employee_id: null,
    designation_id: null,
    gender: null,
    mobile: null,
    dob: null,
    joining_date: null,
    employee_type: null,
    employee_category: null,
    status: 1,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'Employee created successfully.',
    update_success: 'Employee updated successfully.',
  },
}

const EmployeeFormController: FC<any> = (props) => {
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
  } = useCrudFormService(EmployeeApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Employee')
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
      const d = res.data
      const initFormData = {
        name_en: d.name_en,
        name_bn: d.name_bn,
        employee_id: d.employee_id,
        designation_id: d.designation_id,
        gender: d.gender,
        mobile: d.mobile,
        dob: d.dob,
        joining_date: d.joining_date,
        employee_type: d.employee_type,
        employee_category: d.employee_category,
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

  const handleCreate = (values: any): Promise<any> => {
    return BaseCrudFormService.handleCreate({...values})
  }

  const handleUpdate = (values: any): Promise<any> => {
    return BaseCrudFormService.handleUpdate({...values})
  }

  return (
    <div className='form-page-container form-page-container-employee'>
      <DrawerForm
        drawerWidth='60%'
        loading={loading}
        isNewRecord={isNewRecord}
        itemData={itemData}
        modalTitle={modalTitle}
        isSubmitting={isSubmitting}
        isShowForm={isShowForm}
        formRef={formRef}
        initialValues={initialValues}
        component={EmployeeAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(EmployeeFormController)

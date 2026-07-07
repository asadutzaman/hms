import React, {FC, useEffect} from 'react'
import {InsuranceCompanyApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import InsuranceCompanyAddOrEditForm from './InsuranceCompanyForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create Insurance Company',
  itemData: {},
  fields: {
    code: null,
    name: null,
    tpa_type: 'insurer',
    credit_limit: null,
    contact_person: null,
    phone: null,
    email: null,
    address: null,
    description: null,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'The operation performed successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const InsuranceCompanyFormController: FC<any> = (props) => {
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
  } = useCrudFormService(InsuranceCompanyApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Insurance Company')
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
      const initFormData = {
        code: res.data.code,
        name: res.data.name,
        tpa_type: res.data.tpa_type,
        credit_limit: res.data.credit_limit,
        contact_person: res.data.contact_person,
        phone: res.data.phone,
        email: res.data.email,
        address: res.data.address,
        description: res.data.description,
      }
      handleChange(initFormData)
      formRef.setFieldsValue(initFormData)
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

  const handleCreate = (values: any): Promise<any> => BaseCrudFormService.handleCreate({...values})
  const handleUpdate = (values: any): Promise<any> => BaseCrudFormService.handleUpdate({...values})

  return (
    <div className='form-page-container'>
      <DrawerForm
        loading={loading}
        isNewRecord={isNewRecord}
        itemData={itemData}
        modalTitle={modalTitle}
        isSubmitting={isSubmitting}
        isShowForm={isShowForm}
        formRef={formRef}
        initialValues={initialValues}
        component={InsuranceCompanyAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(InsuranceCompanyFormController)

import React, {FC, useEffect} from 'react'
import {DrugApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import DrugAddOrEditForm from './DrugForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create Drug',
  itemData: {},
  fields: {
    logistic_id: null,
    item_category_id: null,
    brand_id: null,
    base_unit_id: null,
    name_en: null,
    name_bn: null,
    description: null,
    reorder_qty: null,
    generic_name: null,
    brand_name: null,
    strength: null,
    dosage_form: 'tablet',
    hsn_code: null,
    is_controlled: false,
    controlled_schedule: null,
    generic_drug_id: null,
    status: 1,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'Drug created successfully.',
    update_success: 'Drug updated successfully.',
  },
}

const DrugFormController: FC<any> = (props) => {
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
  } = useCrudFormService(DrugApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Drug')
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
        logistic_id: d.logistic_id,
        item_category_id: d.item_category_id,
        brand_id: d.brand_id,
        base_unit_id: d.base_unit_id,
        name_en: d.name_en,
        name_bn: d.name_bn,
        description: d.description,
        reorder_qty: d.reorder_qty,
        generic_name: d.generic_name,
        brand_name: d.brand_name,
        strength: d.strength,
        dosage_form: d.dosage_form,
        hsn_code: d.hsn_code,
        is_controlled: d.is_controlled,
        controlled_schedule: d.controlled_schedule,
        generic_drug_id: d.generic_drug_id,
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

  const handleCreate = (values: any): Promise<any> => BaseCrudFormService.handleCreate({...values})

  const handleUpdate = (values: any): Promise<any> => BaseCrudFormService.handleUpdate({...values})

  return (
    <div className='form-page-container form-page-container-drug'>
      <DrawerForm
        drawerWidth='70%'
        loading={loading}
        isNewRecord={isNewRecord}
        itemData={itemData}
        modalTitle={modalTitle}
        isSubmitting={isSubmitting}
        isShowForm={isShowForm}
        formRef={formRef}
        initialValues={initialValues}
        component={DrugAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(DrugFormController)

import React, {FC, useEffect} from 'react'
import dayjs from 'dayjs'
import {RateContractApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import RateContractAddOrEditForm from './RateContractForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'

const initialState = {
  modalTitle: 'Create Rate Contract',
  itemData: {},
  fields: {
    supplier_id: null,
    item_id: null,
    contract_price: null,
    valid_from: null,
    valid_to: null,
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'The operation performed successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const RateContractFormController: FC<any> = (props) => {
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
  } = useCrudFormService(RateContractApi, initialState, props)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Rate Contract')
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
      const initFormDta = {
        supplier_id: res.data.supplier_id,
        item_id: res.data.item_id,
        contract_price: res.data.contract_price,
        valid_from: res.data.valid_from ? dayjs(res.data.valid_from) : null,
        valid_to: res.data.valid_to ? dayjs(res.data.valid_to) : null,
      }
      handleChange(initFormDta)
      formRef.setFieldsValue(initFormDta)
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
    <div className='form-page-container form-page-container-example'>
      <DrawerForm
        loading={loading}
        isNewRecord={isNewRecord}
        itemData={itemData}
        modalTitle={modalTitle}
        isSubmitting={isSubmitting}
        isShowForm={isShowForm}
        drawerWidth={'50%'}
        formRef={formRef}
        initialValues={initialValues}
        component={RateContractAddOrEditForm}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default React.memo(RateContractFormController)

import React, {FC, useEffect, useState} from 'react'
import dayjs from 'dayjs'
import {PurchaseOrderApi} from 'src/app/api'
import DrawerForm from 'src/app/components/Drawer/DrawerForm'
import PurchaseOrderAddOrEditForm from './PurchaseOrderForm.form'
import {useCrudFormService} from 'src/app/hooks/crud/useCrudFormService'
import {Message} from 'src/app/utils'

const initialState = {
  modalTitle: 'Create Purchase Order',
  itemData: {},
  fields: {
    supplier_id: null,
    order_date: null,
    expected_delivery_date: null,
    notes: null,
    process_status: 'DRAFT',
  },
  isNewRecord: true,
  loading: false,
  message: {
    network_error: 'A network error occurred. Please try again later.',
    create_success: 'The operation performed successfully.',
    update_success: 'The operation performed successfully.',
  },
}

const PurchaseOrderFormController: FC<any> = (props) => {
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
  } = useCrudFormService(PurchaseOrderApi, initialState, props)

  const [poItemList, setPoItemList] = useState<any>([])
  const [isLoadingPoItem, setIsLoadingPoItem] = useState<boolean>(false)

  useEffect(() => {
    if (entityId && isShowForm) {
      setIsNewRecord(false)
      setModalTitle('Edit Purchase Order')
      resetForm()
      handleResetForm()
      loadData()
    } else {
      resetForm()
      handleResetForm()
      setModalTitle(initialState.modalTitle)
      setIsNewRecord(initialState.isNewRecord)
    }
  }, [entityId, reloadForm])

  const loadData = (): void => {
    BaseCrudFormService.loadData().then((res: any) => {
      const initFormDta = {
        supplier_id: res.data.supplier_id,
        order_date: res.data.order_date ? dayjs(res.data.order_date) : null,
        expected_delivery_date: res.data.expected_delivery_date
          ? dayjs(res.data.expected_delivery_date)
          : null,
        notes: res.data.notes,
        process_status: res.data.process_status,
        status: res.data.status,
      }

      if (res.data.purchase_order_items_list_data.length > 0) {
        let dataArray: any[] = []
        res.data.purchase_order_items_list_data.map((item: any) => {
          let dataObj = {
            id: item.id,
            item_id: item.item_id,
            name: item.item_info.name_en ?? item.item_info.name_bn,
            unit_price: item.unit_price,
            quantity: item.quantity,
            line_total: item.line_total,
            received_quantity: item.received_quantity,
            remarks: item.remarks,
          }
          dataArray.push(dataObj)
        })
        setPoItemList(dataArray)
      } else {
        setPoItemList([])
      }

      handleChange(initFormDta)
      formRef.setFieldsValue(initFormDta)
    })
  }

  const handleItemSelect = (value: any, option: any) => {
    formRef.setFieldsValue({item_id: value})
    let itemExist = poItemList.find((item: any) => item.item_id == value)
    if (itemExist) {
      Message.error('Item already exists')
    } else {
      setPoItemList((prev: any) => {
        let newObj = {
          item_id: value,
          name: option.label,
          unit_price: 0,
          quantity: 1,
          line_total: 0,
          remarks: '',
        }
        return [...prev, newObj]
      })
    }
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
      poItemsList: poItemList.filter((item: any) => item.quantity !== 0 && item.quantity !== null),
    }
    return BaseCrudFormService.handleCreate(payload)
  }

  const handleUpdate = (values: any): Promise<any> => {
    const payload = {
      ...values,
      poItemsList: poItemList.filter((item: any) => item.quantity !== 0 && item.quantity !== null),
    }
    return BaseCrudFormService.handleUpdate(payload)
  }

  const handleResetForm = () => {
    resetForm()
    setPoItemList([])
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
        drawerWidth={'70%'}
        formRef={formRef}
        initialValues={initialValues}
        component={PurchaseOrderAddOrEditForm}
        isLoadingPoItem={isLoadingPoItem}
        setIsLoadingPoItem={setIsLoadingPoItem}
        handleItemSelect={handleItemSelect}
        handleChange={handleChange}
        handleSubmit={handleSubmit}
        handleSubmitFailed={handleSubmitFailed}
        handleCallbackFunc={handleCallbackFunc}
        poItemList={poItemList}
        setPoItemList={setPoItemList}
      />
    </div>
  )
}

export default React.memo(PurchaseOrderFormController)

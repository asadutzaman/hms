import React, {FC, useEffect} from 'react'
import {useLocation} from 'react-router-dom'
import {parse} from 'query-string'
import {Form} from 'antd'
import {AppointmentApi, DepartmentApi} from 'src/app/api'
import AppointmentListFilter from './AppointmentList.filter'
import AppointmentListing from './AppointmentList.listing'
import AppointmentListPagination from './AppointmentList.pagination'
import AppointmentViewController from '../View/AppointmentView.controller'
import AppointmentFormController from '../Form/AppointmentForm.controller'
import {useCrudListService} from 'src/app/hooks/crud/useCrudListService'

const initialState = {
  search: '',
  entity: {},
  entityId: null,
  listData: [],
  filters: {
    status: '',
    source: '',
    consultation_mode: '',
    department_id: '',
    doctor_id: '',
    date_from: '',
    date_to: '',
  },
  pagination: {
    currentPage: 1,
    pageSize: 10,
  },
  totalCount: 0,
  selectedRowKeys: [],
  sort: 'id desc',
  view: null,
  loading: false,
  isShowView: false,
  isShowForm: false,
  fields: {},
  bulkAction: {
    action: '',
    field: '',
    value: '',
    ids: [] as any,
  },
  message: {
    network_error: 'A network error occurred. Please try again later.',
    delete_success: 'Appointment deleted successfully.',
    delete_confirm_title: 'Delete Appointment',
    delete_confirm: 'Are you sure you want to delete this appointment?',
    delete_bulk_select: 'Please select item(s)',
    delete_bulk_confirm: 'Are you sure you wish to delete selected appointments?',
  },
}

const AppointmentListController: FC<any> = (props) => {
  const location = useLocation()
  const queryParams = parse(location.search)

  const queryState = {
    filters: {
      status: queryParams?.status || initialState.filters.status,
      source: queryParams?.source || initialState.filters.source,
      consultation_mode:
        queryParams?.consultation_mode || initialState.filters.consultation_mode,
      department_id: queryParams?.department_id || initialState.filters.department_id,
      doctor_id: queryParams?.doctor_id || initialState.filters.doctor_id,
      date_from: queryParams?.date_from || initialState.filters.date_from,
      date_to: queryParams?.date_to || initialState.filters.date_to,
    },
  }

  const {
    BaseCrudListService,
    formRef,
    payload,
    setFilters,
    initialValues,
    listData,
    search,
    filters,
    sort,
    pagination,
    totalCount,
    loading,
    entityId,
    isShowView,
    isShowForm,
    setIsShowForm,
    selectedRowKeys,
    bulkAction,
    reloadListing,
    reloadView,
    reloadForm,
  } = useCrudListService(AppointmentApi, queryState, initialState, props)

  useEffect(() => {
    initData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [search, filters, sort, pagination, reloadListing])

  useEffect(() => {
    handleUrl()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [entityId, isShowView, isShowForm])

  useEffect(() => {
    if (bulkAction.action !== '') {
      executeBulkAction()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [bulkAction])

  const initData = async () => {
    await handleUrl()
    await handlePayload()
    await loadData()
  }

  const loadData = (): Promise<any> => BaseCrudListService.loadData()
  const executeBulkAction = (): Promise<any> => BaseCrudListService.executeBulkAction()

  const handleUrl = (): void => {
    let urlObject: any = {}
    if (search) urlObject.q = search
    if (filters.status) urlObject.status = filters.status
    if (filters.source) urlObject.source = filters.source
    if (filters.consultation_mode) urlObject.consultation_mode = filters.consultation_mode
    if (filters.department_id) urlObject.department_id = filters.department_id
    if (filters.doctor_id) urlObject.doctor_id = filters.doctor_id
    if (filters.date_from) urlObject.date_from = filters.date_from
    if (filters.date_to) urlObject.date_to = filters.date_to
    BaseCrudListService.handleUrl(urlObject)
  }

  const processFilters = (): string => {
    let filterString = '1=1'
    if (filters.status) {
      filterString += " AND status='" + filters.status + "'"
    }
    if (filters.source) {
      filterString += " AND source='" + filters.source + "'"
    }
    if (filters.consultation_mode) {
      filterString += " AND consultation_mode='" + filters.consultation_mode + "'"
    }
    if (filters.department_id) {
      filterString += " AND department_id=" + filters.department_id
    }
    if (filters.doctor_id) {
      filterString += " AND doctor_id=" + filters.doctor_id
    }
    if (filters.date_from) {
      filterString += " AND appointment_date>='" + filters.date_from + "'"
    }
    if (filters.date_to) {
      filterString += " AND appointment_date<='" + filters.date_to + "'"
    }
    return BaseCrudListService.processFilters(filterString)
  }

  const processQueryParams = () => BaseCrudListService.processQueryParams({})
  const processOrderBy = (): string => BaseCrudListService.processOrderBy('')

  const handlePayload = (): void => {
    payload.current = {
      $select: '',
      $search: search,
      $filter: processFilters(),
      $queryParams: processQueryParams(),
      $expand:
        'patient,doctor,department,chamber,createdBy,updatedBy,appointmentSlot',
      $orderby: processOrderBy(),
      $top: pagination.pageSize,
      $skip: pagination.pageSize * (pagination.currentPage - 1),
    }
  }

  const onChangeSwitchToggle = (checked: any, record: any) => {
    BaseCrudListService.onChangeSwitchToggle(checked, record)
  }

  const handleTableChange = (pagination: any, filters: any, sorter: any, extra: any) => {
    BaseCrudListService.handleTableChange(pagination, filters, sorter, extra)
  }

  const handleOnChanged = (fieldName: string, value: any, text?: any) => {
    if (fieldName === 'filter_status') {
      setFilters({...filters, status: value})
    } else if (fieldName === 'filter_source') {
      setFilters({...filters, source: value})
    } else if (fieldName === 'filter_consultation_mode') {
      setFilters({...filters, consultation_mode: value})
    } else if (fieldName === 'filter_department_id') {
      setFilters({...filters, department_id: value})
    } else if (fieldName === 'filter_doctor_id') {
      setFilters({...filters, doctor_id: value})
    } else if (fieldName === 'filter_date_from') {
      setFilters({...filters, date_from: value})
    } else if (fieldName === 'filter_date_to') {
      setFilters({...filters, date_to: value})
    }
    BaseCrudListService.handleOnChanged(fieldName, value, text)
  }

  const handleCallbackFunc = (event: any, action: string, recordId?: any, data?: any) => {
    BaseCrudListService.handleCallbackFunc(event, action, recordId, data)
  }

  return (
    <div className='card'>
      <Form
        form={formRef}
        name='appointmentListingFilterForm'
        initialValues={initialValues}
      >
        <AppointmentListFilter
          filters={filters}
          handleOnChanged={handleOnChanged}
          handleCallbackFunc={handleCallbackFunc}
        />
        <AppointmentListing
          loading={loading}
          listData={listData}
          reloadListing={reloadListing}
          selectedRowKeys={selectedRowKeys}
          onChangeSwitchToggle={onChangeSwitchToggle}
          handleOnChanged={handleOnChanged}
          handleTableChange={handleTableChange}
          handleCallbackFunc={handleCallbackFunc}
        />
        <AppointmentListPagination
          pagination={pagination}
          totalCount={totalCount}
          handleOnChanged={handleOnChanged}
        />
      </Form>
      <AppointmentViewController
        entityId={entityId}
        reloadView={reloadView}
        isShowView={isShowView}
        handleCallbackFunc={handleCallbackFunc}
      />
      <AppointmentFormController
        entityId={entityId}
        reloadForm={reloadForm}
        isShowForm={isShowForm}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default AppointmentListController

import React, {FC, useEffect} from 'react'
import {useLocation} from 'react-router-dom'
import {parse} from 'query-string'
import {Form} from 'antd'
import {DoctorScheduleApi} from 'src/app/api'
import {useCrudListService} from 'src/app/hooks/crud/useCrudListService'
import DoctorScheduleListFilter from './DoctorScheduleList.filter'
import DoctorScheduleListing from './DoctorScheduleList.listing'
import DoctorScheduleListPagination from './DoctorScheduleList.pagination'
import DoctorScheduleViewController from '../View/DoctorScheduleView.controller'
import DoctorScheduleFormController from '../Form/DoctorScheduleForm.controller'

const initialState = {
  search: '',
  entity: {},
  entityId: null,
  listData: [],
  filters: {
    status: '',
    schedule_type: '',
    consultation_mode: '',
    doctor_id: '',
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
    delete_success: 'Doctor schedule deleted successfully.',
    delete_confirm_title: 'Delete Doctor Schedule',
    delete_confirm: 'Are you sure you want to delete this schedule?',
    delete_bulk_select: 'Please select item(s)',
    delete_bulk_confirm: 'Are you sure you wish to delete selected schedules?',
  },
}

const DoctorScheduleListController: FC<any> = (props) => {
  const location = useLocation()
  const queryParams = parse(location.search)

  const queryState = {
    filters: {
      status: queryParams?.status || initialState.filters.status,
      schedule_type:
        queryParams?.schedule_type || initialState.filters.schedule_type,
      consultation_mode:
        queryParams?.consultation_mode ||
        initialState.filters.consultation_mode,
      doctor_id: queryParams?.doctor_id || initialState.filters.doctor_id,
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
    selectedRowKeys,
    bulkAction,
    reloadListing,
    reloadView,
    reloadForm,
  } = useCrudListService(DoctorScheduleApi, queryState, initialState, props)

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
  const executeBulkAction = (): Promise<any> =>
    BaseCrudListService.executeBulkAction()

  const handleUrl = (): void => {
    let urlObject: any = {}
    if (search) urlObject.q = search
    if (filters.status) urlObject.status = filters.status
    if (filters.schedule_type) urlObject.schedule_type = filters.schedule_type
    if (filters.consultation_mode)
      urlObject.consultation_mode = filters.consultation_mode
    if (filters.doctor_id) urlObject.doctor_id = filters.doctor_id
    BaseCrudListService.handleUrl(urlObject)
  }

  const processFilters = (): string => {
    let filterString = '1=1'
    if (filters.status) {
      filterString += " AND status='" + filters.status + "'"
    }
    if (filters.schedule_type) {
      filterString += " AND schedule_type='" + filters.schedule_type + "'"
    }
    if (filters.consultation_mode) {
      filterString +=
        " AND consultation_mode='" + filters.consultation_mode + "'"
    }
    if (filters.doctor_id) {
      filterString += ' AND doctor_id=' + filters.doctor_id
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
      $expand: 'doctor,department,chamber,slots',
      $orderby: processOrderBy(),
      $top: pagination.pageSize,
      $skip: pagination.pageSize * (pagination.currentPage - 1),
    }
  }

  const onChangeSwitchToggle = (checked: any, record: any) => {
    BaseCrudListService.onChangeSwitchToggle(checked, record)
  }

  const handleTableChange = (
    pagination: any,
    filters: any,
    sorter: any,
    extra: any
  ) => {
    BaseCrudListService.handleTableChange(pagination, filters, sorter, extra)
  }

  const handleOnChanged = (fieldName: string, value: any, text?: any) => {
    if (fieldName === 'filter_status') {
      setFilters({...filters, status: value})
    } else if (fieldName === 'filter_schedule_type') {
      setFilters({...filters, schedule_type: value})
    } else if (fieldName === 'filter_consultation_mode') {
      setFilters({...filters, consultation_mode: value})
    } else if (fieldName === 'filter_doctor_id') {
      setFilters({...filters, doctor_id: value})
    }
    BaseCrudListService.handleOnChanged(fieldName, value, text)
  }

  const handleCallbackFunc = (
    event: any,
    action: string,
    recordId?: any,
    data?: any
  ) => {
    BaseCrudListService.handleCallbackFunc(event, action, recordId, data)
  }

  return (
    <div className='card'>
      <Form
        form={formRef}
        name='doctorScheduleListingFilterForm'
        initialValues={initialValues}
      >
        <DoctorScheduleListFilter
          filters={filters}
          handleOnChanged={handleOnChanged}
          handleCallbackFunc={handleCallbackFunc}
        />
        <DoctorScheduleListing
          loading={loading}
          listData={listData}
          reloadListing={reloadListing}
          selectedRowKeys={selectedRowKeys}
          onChangeSwitchToggle={onChangeSwitchToggle}
          handleOnChanged={handleOnChanged}
          handleTableChange={handleTableChange}
          handleCallbackFunc={handleCallbackFunc}
        />
        <DoctorScheduleListPagination
          pagination={pagination}
          totalCount={totalCount}
          handleOnChanged={handleOnChanged}
        />
      </Form>
      <DoctorScheduleViewController
        entityId={entityId}
        reloadView={reloadView}
        isShowView={isShowView}
        handleCallbackFunc={handleCallbackFunc}
      />
      <DoctorScheduleFormController
        entityId={entityId}
        reloadForm={reloadForm}
        isShowForm={isShowForm}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default DoctorScheduleListController
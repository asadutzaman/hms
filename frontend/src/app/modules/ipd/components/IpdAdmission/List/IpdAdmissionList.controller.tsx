import React, { FC, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { parse } from 'query-string';
import { Form } from 'antd';
import IpdAdmissionListFilter from './IpdAdmissionList.filter';
import IpdAdmissionListing from './IpdAdmissionList.listing';
import IpdAdmissionListPagination from './IpdAdmissionList.pagination';
import IpdAdmissionViewController from '../View/IpdAdmissionView.controller';
import IpdAdmissionFormController from '../Form/IpdAdmissionForm.controller';
import { useCrudListService } from 'src/app/hooks/crud/useCrudListService';
import { IpdAdmissionApi } from 'src/app/api';

const initialState = {
  search: '',
  entity: {},
  entityId: null,
  listData: [],
  filters: {
    admission_status: '',
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
    delete_success: 'Delete Admission successfully',
    delete_confirm_title: 'Delete Admission',
    delete_confirm: 'Are you sure you want to delete this admission record?',
    delete_bulk_select: 'Please select item(s)',
    delete_bulk_confirm: 'Are you sure you wish to delete selected admissions?',
  },
};

const IpdAdmissionListController: FC<any> = (props) => {
  const location = useLocation();
  const queryParams = parse(location.search);

  const queryState = {
    filters: {
      admission_status: queryParams?.admission_status || initialState.filters.admission_status,
    },
  };

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
  } = useCrudListService(IpdAdmissionApi, queryState, initialState, props);

  useEffect(() => {
    initData();
  }, [search, filters, sort, pagination, reloadListing]);

  useEffect(() => {
    handleUrl();
  }, [entityId, isShowView, isShowForm]);

  useEffect(() => {
    if (bulkAction.action !== '') {
      executeBulkAction();
    }
  }, [bulkAction]);

  const initData = async () => {
    await handleUrl();
    await handlePayload();
    await loadData();
  };

  const loadData = (): Promise<any> => {
    return BaseCrudListService.loadData();
  };

  const executeBulkAction = (): Promise<any> => {
    return BaseCrudListService.executeBulkAction();
  };

  const handleUrl = (): void => {
    let urlObject: any = {};

    if (search) {
      urlObject.q = search;
    }
    if (filters.admission_status) {
      urlObject.admission_status = filters.admission_status;
    }
    BaseCrudListService.handleUrl(urlObject);
  };

  const processFilters = (): string => {
    let filterString = '';

    if (filters.admission_status) {
      filterString += " admission_status='" + filters.admission_status + "'";
    }

    return BaseCrudListService.processFilters(filterString);
  };

  const processQueryParams = () => {
    let filterString = {};
    return BaseCrudListService.processQueryParams(filterString);
  };

  const processOrderBy = (): string => {
    let orderByString = '';
    return BaseCrudListService.processOrderBy(orderByString);
  };

  const handlePayload = (): void => {
    payload.current = {
      $select: '',
      $search: search,
      $filter: processFilters(),
      $queryParams: processQueryParams(),
      $expand: 'patient,ward,bed',
      $orderby: processOrderBy(),
      $top: pagination.pageSize,
      $skip: pagination.pageSize * (pagination.currentPage - 1),
    };
  };

  const onChangeSwitchToggle = (checked: any, record: any) => {
    BaseCrudListService.onChangeSwitchToggle(checked, record);
  };

  const handleTableChange = (
    pagination: any,
    filters: any,
    sorter: any,
    extra: any
  ) => {
    BaseCrudListService.handleTableChange(pagination, filters, sorter, extra);
  };

  const handleOnChanged = (fieldName: string, value: any, text?: any) => {
    if (fieldName === 'filter_admission_status') {
      setFilters({
        ...filters,
        admission_status: value,
      });
    }
    BaseCrudListService.handleOnChanged(fieldName, value, text);
  };

  const handleCallbackFunc = (
    event: any,
    action: string,
    recordId?: any,
    data?: any
  ) => {
    BaseCrudListService.handleCallbackFunc(event, action, recordId, data);
  };

  return (
    <div className="card">
      <Form
        form={formRef}
        name="ipdAdmissionListingFilterForm"
        initialValues={initialValues}
      >
        <IpdAdmissionListFilter
          filters={filters}
          handleOnChanged={handleOnChanged}
          handleCallbackFunc={handleCallbackFunc}
        />
        <IpdAdmissionListing
          loading={loading}
          listData={listData}
          reloadListing={reloadListing}
          selectedRowKeys={selectedRowKeys}
          onChangeSwitchToggle={onChangeSwitchToggle}
          handleOnChanged={handleOnChanged}
          handleTableChange={handleTableChange}
          handleCallbackFunc={handleCallbackFunc}
        />
        <IpdAdmissionListPagination
          pagination={pagination}
          totalCount={totalCount}
          handleOnChanged={handleOnChanged}
        />
      </Form>
      <IpdAdmissionViewController
        entityId={entityId}
        reloadView={reloadView}
        isShowView={isShowView}
        handleCallbackFunc={handleCallbackFunc}
      />
      <IpdAdmissionFormController
        entityId={entityId}
        reloadForm={reloadForm}
        isShowForm={isShowForm}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  );
};

export default IpdAdmissionListController;

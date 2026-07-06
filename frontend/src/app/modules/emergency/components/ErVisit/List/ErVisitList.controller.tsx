import React, {FC, useEffect} from 'react';
import {Form} from 'antd';
import ErVisitListFilter from './ErVisitList.filter';
import ErVisitListing from './ErVisitList.listing';
import ErVisitListPagination from './ErVisitList.pagination';
import ErVisitViewController from '../View/ErVisitView.controller';
import ErVisitFormController from '../Form/ErVisitForm.controller';
import {useCrudListService} from 'src/app/hooks/crud/useCrudListService';
import {ErVisitApi} from 'src/app/api';

const initialState = {
  search: '',
  entity: {},
  entityId: null,
  listData: [],
  filters: {},
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
    delete_success: 'Delete ER Visit successfully',
    delete_confirm_title: 'Delete ER Visit',
    delete_confirm: 'Are you sure you want to delete this ER visit record?',
    delete_bulk_select: 'Please select item(s)',
    delete_bulk_confirm: 'Are you sure you wish to delete selected ER visits?',
  },
};

const ErVisitListController: FC<any> = (props) => {
  const {
    BaseCrudListService,
    formRef,
    payload,
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
  } = useCrudListService(ErVisitApi, {}, initialState, props);

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

  const loadData = (): Promise<any> => BaseCrudListService.loadData();

  const executeBulkAction = (): Promise<any> => BaseCrudListService.executeBulkAction();

  const handleUrl = (): void => {
    let urlObject: any = {};
    if (search) {
      urlObject.q = search;
    }
    BaseCrudListService.handleUrl(urlObject);
  };

  const processFilters = (): string => {
    return BaseCrudListService.processFilters('');
  };

  const processQueryParams = () => {
    return BaseCrudListService.processQueryParams({});
  };

  const processOrderBy = (): string => {
    return BaseCrudListService.processOrderBy('');
  };

  const handlePayload = (): void => {
    payload.current = {
      $select: '',
      $search: search,
      $filter: processFilters(),
      $queryParams: processQueryParams(),
      $orderby: processOrderBy(),
      $top: pagination.pageSize,
      $skip: pagination.pageSize * (pagination.currentPage - 1),
    };
  };

  const onChangeSwitchToggle = (checked: any, record: any) => {
    BaseCrudListService.onChangeSwitchToggle(checked, record);
  };

  const handleTableChange = (pagination: any, filters: any, sorter: any, extra: any) => {
    BaseCrudListService.handleTableChange(pagination, filters, sorter, extra);
  };

  const handleOnChanged = (fieldName: string, value: any, text?: any) => {
    BaseCrudListService.handleOnChanged(fieldName, value, text);
  };

  const handleCallbackFunc = (event: any, action: string, recordId?: any, data?: any) => {
    BaseCrudListService.handleCallbackFunc(event, action, recordId, data);
  };

  return (
    <div className="card">
      <Form form={formRef} name="erVisitListingFilterForm" initialValues={initialValues}>
        <ErVisitListFilter handleOnChanged={handleOnChanged} handleCallbackFunc={handleCallbackFunc} />
        <ErVisitListing
          loading={loading}
          listData={listData}
          reloadListing={reloadListing}
          selectedRowKeys={selectedRowKeys}
          onChangeSwitchToggle={onChangeSwitchToggle}
          handleOnChanged={handleOnChanged}
          handleTableChange={handleTableChange}
          handleCallbackFunc={handleCallbackFunc}
        />
        <ErVisitListPagination pagination={pagination} totalCount={totalCount} handleOnChanged={handleOnChanged} />
      </Form>
      <ErVisitViewController entityId={entityId} reloadView={reloadView} isShowView={isShowView} handleCallbackFunc={handleCallbackFunc} />
      <ErVisitFormController entityId={entityId} reloadForm={reloadForm} isShowForm={isShowForm} handleCallbackFunc={handleCallbackFunc} />
    </div>
  );
};

export default ErVisitListController;

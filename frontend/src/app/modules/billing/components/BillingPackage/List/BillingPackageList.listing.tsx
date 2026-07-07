import React, {FC} from 'react'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {Tag} from 'antd'
import {BillingPackageAction} from '../Actions/BillingPackage.actions'
import {useLang} from 'src/app/hooks/useLang'

const BillingPackageListing: FC<any> = (props) => {
  const {t} = useLang()
  const {
    loading,
    listData,
    selectedRowKeys,
    onChangeSwitchToggle,
    handleOnChanged,
    handleTableChange,
    handleCallbackFunc,
  } = props

  const columns = [
    {
      dataIndex: 'code',
      key: 'code',
      title: t('Code'),
      width: '15%',
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={BillingPackageAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-bolder cursor-pointer'>{text}</span>
        </ViewAction>
      ),
    },
    {dataIndex: 'name', key: 'name', title: t('Name'), width: '30%'},
    {
      dataIndex: 'package_type',
      key: 'package_type',
      title: t('Applies To'),
      width: '15%',
      render: (v: string) => <Tag>{(v || '').toUpperCase()}</Tag>,
    },
    {dataIndex: 'fixed_price', key: 'fixed_price', title: t('Fixed Price'), width: '15%'},
    {
      dataIndex: 'is_active',
      key: 'is_active',
      title: t('Active'),
      width: '10%',
      render: (v: any) => (v ? t('Yes') : t('No')),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      width: '15%',
      align: 'center',
      render: (text: string, record: any) => (
        <ListItemAction
          entityId={record.id}
          actionList={BillingPackageAction.LIST_ITEM_ACTION}
          handleCallbackFunc={handleCallbackFunc}
        />
      ),
    },
  ]

  return (
    <div className='px-6'>
      <AntTable
        className='table-layout'
        rowSelection={false}
        rowSelectionPermission='auth:billing-package:multiSelect'
        selectedRowKeys={selectedRowKeys}
        dataSource={listData}
        columns={columns}
        loading={loading}
        handleOnChanged={handleOnChanged}
        onChange={handleTableChange}
      />
    </div>
  )
}

export default React.memo(BillingPackageListing)

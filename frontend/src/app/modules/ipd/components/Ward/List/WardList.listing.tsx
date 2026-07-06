import React, {FC} from 'react'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {WardAction} from '../Actions/Ward.actions'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'

const wardTypeLabels: Record<string, string> = {
  general: 'General',
  semi_private: 'Semi Private',
  private: 'Private',
  icu: 'ICU',
  emergency: 'Emergency',
}

const WardListing: FC<any> = (props) => {
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
      dataIndex: 'name',
      key: 'name',
      title: t('Ward Name'),
      sorter: true,
      width: '25%',
      render: (text: string, record: any, index: number) => (
        <ViewAction
          entityId={record.id}
          actionItem={WardAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-bolder cursor-pointer'>{text}</span>
        </ViewAction>
      ),
    },
    {
      dataIndex: 'branch_name',
      key: 'branch_name',
      title: t('Branch'),
      width: '20%',
    },
    {
      dataIndex: 'ward_type',
      key: 'ward_type',
      title: t('Ward Type'),
      width: '15%',
      render: (value: string) => wardTypeLabels[value] || value,
    },
    {
      dataIndex: 'floor',
      key: 'floor',
      title: t('Floor'),
      width: '10%',
    },
    {
      dataIndex: 'created_by_name',
      key: 'created_by_name',
      title: t('Created By'),
      width: '15%',
    },
    {
      dataIndex: 'created_at',
      key: 'created_at',
      title: t('Created Time'),
      sorter: true,
      width: '15%',
      render: (value: any) => DateTimeUtils.formatDateTimeA(value),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      width: '10%',
      align: 'center',
      render: (text: string, record: any, index: number) => (
        <ListItemAction
          entityId={record.id}
          actionList={WardAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:ward:multiSelect'
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

export default React.memo(WardListing)

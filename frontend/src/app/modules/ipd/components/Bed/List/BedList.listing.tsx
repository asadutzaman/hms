import React, {FC} from 'react'
import {Tag} from 'antd'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {BedAction} from '../Actions/Bed.actions'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'

const bedStatusColor = (status: string): string => {
  switch (status) {
    case 'vacant':
      return 'green'
    case 'occupied':
      return 'red'
    case 'reserved':
      return 'gold'
    case 'cleaning':
      return 'blue'
    case 'maintenance':
      return 'default'
    default:
      return 'default'
  }
}

const BedListing: FC<any> = (props) => {
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
      dataIndex: 'bed_number',
      key: 'bed_number',
      title: t('Bed Number'),
      sorter: true,
      width: '20%',
      render: (text: string, record: any, index: number) => (
        <ViewAction
          entityId={record.id}
          actionItem={BedAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-bolder cursor-pointer'>{text}</span>
        </ViewAction>
      ),
    },
    {
      dataIndex: 'ward_name',
      key: 'ward_name',
      title: t('Ward'),
      width: '20%',
    },
    {
      dataIndex: 'bed_type',
      key: 'bed_type',
      title: t('Bed Type'),
      width: '15%',
    },
    {
      dataIndex: 'daily_rate',
      key: 'daily_rate',
      title: t('Daily Rate'),
      width: '15%',
    },
    {
      dataIndex: 'bed_status',
      key: 'bed_status',
      title: t('Bed Status'),
      width: '15%',
      render: (value: string) => (
        <Tag color={bedStatusColor(value)} className='text-capitalize'>
          {value}
        </Tag>
      ),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      width: '15%',
      align: 'center',
      render: (text: string, record: any, index: number) => (
        <ListItemAction
          entityId={record.id}
          actionList={BedAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:bed:multiSelect'
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

export default React.memo(BedListing)

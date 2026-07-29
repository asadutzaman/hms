import React, {FC} from 'react'
import {CommonUtils, DateTimeUtils} from 'src/app/utils'
import AntTable from 'src/app/components/Table/AntTable'
import {DailyReviewAction as ACT} from '../Actions/DailyReview.actions'
import ViewAction from 'src/app/components/Actions/ViewAction'
import ListItemAction from 'src/app/components/Actions/ListItemAction'

const DailyReviewListing: FC<any> = (props) => {
  const {loading, listData, selectedRowKeys, onChangeSwitchToggle, handleOnChanged, handleTableChange, handleCallbackFunc} = props
  const columns = [
    {
      dataIndex: 'ipd_admission_id', key: 'ipd_admission_id', title: 'Admission ID', sorter: true, width: '22%',
      render: (text: any, record: any) => (
        <ViewAction entityId={record.id} actionItem={ACT.COMMON_ACTION.VIEW} defaultViewText={text} handleCallbackFunc={handleCallbackFunc}>
          <span className='grid-row-view-action'>{text}</span>
        </ViewAction>
      ),
    },
    {
      dataIndex: 'review_date', key: 'review_date', title: 'Review Date', sorter: true, width: '15%',
    },
    {
      dataIndex: 'created_at', key: 'created_at', title: 'Created', sorter: true, width: '16%',
      render: (value: any) => DateTimeUtils.formatDateTimeA(value),
    },
    {
      dataIndex: 'status', key: 'status', title: 'Status', sorter: true, width: '10%',
      render: (text: any, record: any) => CommonUtils.displaySwitchToggleBtn(record, record.status, onChangeSwitchToggle),
    },
    {
      dataIndex: 'action', key: 'action', title: 'Action', width: '10%', align: 'center',
      render: (text: any, record: any) => (
        <ListItemAction entityId={record.id} actionList={ACT.LIST_ITEM_ACTION} handleCallbackFunc={handleCallbackFunc} />
      ),
    },
  ]
  return (
    <div className='px-6'>
      <AntTable className='table-layout' rowSelection={false} scroll={{y: 500}} rowSelectionPermission='hms:daily-review:multiSelect'
        selectedRowKeys={selectedRowKeys} dataSource={listData} columns={columns} loading={loading}
        handleOnChanged={handleOnChanged} onChange={handleTableChange} />
    </div>
  )
}
export default React.memo(DailyReviewListing)

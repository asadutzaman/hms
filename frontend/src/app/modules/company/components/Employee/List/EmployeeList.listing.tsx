import React, {FC} from 'react'
import {CommonUtils, DateTimeUtils} from 'src/app/utils'
import AntTable from 'src/app/components/Table/AntTable'
import {EmployeeAction} from '../Actions/Employee.actions'
import ViewAction from 'src/app/components/Actions/ViewAction'
import ListItemAction from 'src/app/components/Actions/ListItemAction'

const EmployeeListing: FC<any> = (props) => {
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
      dataIndex: 'employee_id',
      key: 'employee_id',
      title: 'Employee ID',
      sorter: true,
      width: '12%',
    },
    {
      dataIndex: 'name_en',
      key: 'name_en',
      title: 'Name (EN)',
      sorter: true,
      width: '20%',
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={EmployeeAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action'>{text}</span>
        </ViewAction>
      ),
    },
    {
      dataIndex: 'designation_name',
      key: 'designation_name',
      title: 'Designation',
      sorter: false,
      width: '18%',
    },
    {
      dataIndex: 'mobile',
      key: 'mobile',
      title: 'Mobile',
      sorter: true,
      width: '13%',
    },
    {
      dataIndex: 'joining_date',
      key: 'joining_date',
      title: 'Joining Date',
      sorter: true,
      width: '12%',
      render: (value: any) => DateTimeUtils.formatDateTimeA(value),
    },
    {
      dataIndex: 'status',
      key: 'status',
      title: 'Status',
      sorter: true,
      width: '10%',
      render: (text: string, record: any) =>
        CommonUtils.displaySwitchToggleBtn(record, record.status, onChangeSwitchToggle),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: 'Action',
      width: '10%',
      align: 'center',
      render: (text: string, record: any) => (
        <ListItemAction
          entityId={record.id}
          actionList={EmployeeAction.LIST_ITEM_ACTION}
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
        scroll={{y: 500}}
        rowSelectionPermission='auth:employee:multiSelect'
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

export default React.memo(EmployeeListing)

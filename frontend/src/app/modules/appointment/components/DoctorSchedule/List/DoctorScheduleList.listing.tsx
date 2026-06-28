import React, {FC} from 'react'
import {Badge} from 'react-bootstrap'
import AntTable from 'src/app/components/Table/AntTable'
import ViewAction from 'src/app/components/Actions/ViewAction'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import {CommonUtils, DateTimeUtils} from 'src/app/utils'
import {DoctorScheduleAction} from '../Actions/DoctorSchedule.actions'

const DoctorScheduleListing: FC<any> = (props) => {
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
      title: 'Schedule Name',
      sorter: true,
      width: '20%',
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={DoctorScheduleAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-semibold'>{text}</span>
        </ViewAction>
      ),
    },
    {
      dataIndex: 'doctor_name',
      key: 'doctor_name',
      title: 'Doctor',
      sorter: true,
      width: '13%',
      render: (text: string, record: any) =>
        text || record.doctor?.name_en || record.doctor?.name || '-',
    },
    {
      dataIndex: 'department_name',
      key: 'department_name',
      title: 'Department',
      width: '10%',
      render: (_text: string, record: any) =>
        record.department?.name || '-',
    },
    {
      dataIndex: 'schedule_type',
      key: 'schedule_type',
      title: 'Type',
      width: '8%',
      render: (value: string) =>
        value ? (
          <span className='text-capitalize'>{value.replace('_', ' ')}</span>
        ) : (
          '-'
        ),
    },
    {
      dataIndex: 'consultation_mode',
      key: 'consultation_mode',
      title: 'Mode',
      width: '9%',
      render: (value: string) =>
        value ? (
          <span className='text-capitalize'>{value.replace('_', ' ')}</span>
        ) : (
          '-'
        ),
    },
    {
      dataIndex: 'effective_from',
      key: 'effective_from',
      title: 'Effective From',
      width: '10%',
      render: (value: any) => DateTimeUtils.formatDate(value),
    },
    {
      dataIndex: 'effective_to',
      key: 'effective_to',
      title: 'Effective To',
      width: '10%',
      render: (value: any) =>
        value ? DateTimeUtils.formatDate(value) : 'Indefinite',
    },
    {
      dataIndex: 'slot_count',
      key: 'slot_count',
      title: 'Slots',
      width: '6%',
      align: 'center',
      render: (value: number, record: any) =>
        value != null ? value : record.slots?.length || 0,
    },
    {
      dataIndex: 'is_default',
      key: 'is_default',
      title: 'Default',
      width: '7%',
      render: (value: boolean) =>
        value ? <Badge bg='success'>Yes</Badge> : <Badge bg='secondary'>No</Badge>,
    },
    {
      dataIndex: 'status',
      key: 'status',
      title: 'Status',
      width: '7%',
      render: (text: string, record: any) =>
        CommonUtils.displaySwitchToggleBtn(record, record.status, onChangeSwitchToggle),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: 'Action',
      width: '8%',
      align: 'center',
      render: (_text: string, record: any) => (
        <ListItemAction
          entityId={record.id}
          actionList={DoctorScheduleAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:doctor-schedule:multiSelect'
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

export default React.memo(DoctorScheduleListing)
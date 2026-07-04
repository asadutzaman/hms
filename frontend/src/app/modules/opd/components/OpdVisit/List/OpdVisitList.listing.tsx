import React, {FC} from 'react'
import {Badge} from 'react-bootstrap'
import AntTable from 'src/app/components/Table/AntTable'
import ViewAction from 'src/app/components/Actions/ViewAction'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import {DateTimeUtils} from 'src/app/utils'
import {OpdVisitAction} from '../Actions/OpdVisit.actions'

const statusColor = (status: string): string => {
  switch (status) {
    case 'scheduled':
      return 'primary'
    case 'confirmed':
      return 'info'
    case 'checked_in':
      return 'warning'
    case 'in_consultation':
      return 'warning'
    case 'completed':
      return 'success'
    case 'cancelled':
      return 'danger'
    case 'no_show':
      return 'danger'
    case 'rescheduled':
      return 'secondary'
    default:
      return 'secondary'
  }
}

const OpdVisitListing: FC<any> = (props) => {
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
      dataIndex: 'opd_no',
      key: 'opd_no',
      title: 'OPD No',
      sorter: true,
      width: '11%',
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={OpdVisitAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-semibold'>{text}</span>
        </ViewAction>
      ),
    },
    {
      dataIndex: 'patient_name',
      key: 'patient_name',
      title: 'Patient',
      sorter: true,
      width: '15%',
      render: (text: string, record: any) => (
        <div>
          <div className='fw-semibold'>{text || record.patient?.full_name || '-'}</div>
          <div className='text-muted fs-7'>
            {record.patient?.primary_phone || record.patient?.mrn || ''}
          </div>
        </div>
      ),
    },
    {
      dataIndex: 'doctor_name',
      key: 'doctor_name',
      title: 'Doctor',
      sorter: true,
      width: '12%',
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
      dataIndex: 'visit_date',
      key: 'visit_date',
      title: 'Date',
      sorter: true,
      width: '9%',
      render: (value: any) => DateTimeUtils.formatDate(value),
    },
    {
      dataIndex: 'token_number',
      key: 'token_number',
      title: 'Token',
      width: '6%',
      align: 'center',
      render: (value: any) => value || '-',
    },
    {
      dataIndex: 'visit_type',
      key: 'visit_type',
      title: 'Visit Type',
      width: '10%',
      render: (value: string) =>
        value ? (
          <span className='text-capitalize'>{value.replace('_', ' ')}</span>
        ) : (
          '-'
        ),
    },
    {
      dataIndex: 'status',
      key: 'status',
      title: 'Status',
      width: '9%',
      render: (value: string) => (
        <Badge bg={statusColor(value)} className='text-capitalize'>
          {(value || 'unknown').replace('_', ' ')}
        </Badge>
      ),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: 'Action',
      width: '7%',
      align: 'center',
      render: (_text: string, record: any) => (
        <ListItemAction
          entityId={record.id}
          actionList={OpdVisitAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:opd:multiSelect'
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

export default React.memo(OpdVisitListing)

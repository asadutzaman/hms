import React, {FC} from 'react'
import {Tag} from 'antd'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {ErVisitAction} from '../Actions/ErVisit.actions'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'

const statusColor = (status: string): string => {
  switch (status) {
    case 'waiting_triage':
      return 'red'
    case 'triaged':
      return 'gold'
    case 'in_treatment':
      return 'blue'
    case 'admitted':
      return 'purple'
    case 'discharged':
      return 'green'
    case 'lwbs':
      return 'default'
    case 'deceased':
      return 'default'
    default:
      return 'default'
  }
}

const ErVisitListing: FC<any> = (props) => {
  const {t} = useLang()
  const {loading, listData, selectedRowKeys, onChangeSwitchToggle, handleOnChanged, handleTableChange, handleCallbackFunc} = props

  const columns = [
    {
      dataIndex: 'er_visit_no',
      key: 'er_visit_no',
      title: t('Visit No'),
      sorter: true,
      width: '15%',
      render: (text: string, record: any) => (
        <ViewAction entityId={record.id} actionItem={ErVisitAction.COMMON_ACTION.VIEW} defaultViewText={text} handleCallbackFunc={handleCallbackFunc}>
          <span className='grid-row-view-action fw-bolder cursor-pointer'>{text}</span>
        </ViewAction>
      ),
    },
    {
      dataIndex: 'patient_name',
      key: 'patient_name',
      title: t('Patient'),
      width: '18%',
      render: (text: string, record: any) => (
        <span>
          {text} {record.mrn ? <span className='text-muted fs-8'>(MRN {record.mrn})</span> : ''}
        </span>
      ),
    },
    {dataIndex: 'chief_complaint', key: 'chief_complaint', title: t('Chief Complaint'), width: '25%'},
    {
      dataIndex: 'arrival_at',
      key: 'arrival_at',
      title: t('Arrival'),
      sorter: true,
      width: '15%',
      render: (value: any) => DateTimeUtils.formatDateTimeA(value),
    },
    {
      dataIndex: 'er_status',
      key: 'er_status',
      title: t('Status'),
      width: '12%',
      render: (v: string, row: any) => (
        <Tag color={statusColor(v)} className='text-capitalize'>
          {row.er_status_label || v}
        </Tag>
      ),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      width: '10%',
      align: 'center',
      render: (text: string, record: any) => (
        <ListItemAction entityId={record.id} actionList={ErVisitAction.LIST_ITEM_ACTION} handleCallbackFunc={handleCallbackFunc} />
      ),
    },
  ]

  return (
    <div className='px-6'>
      <AntTable
        className='table-layout'
        rowSelection={false}
        rowSelectionPermission='auth:er-visit:multiSelect'
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

export default React.memo(ErVisitListing)

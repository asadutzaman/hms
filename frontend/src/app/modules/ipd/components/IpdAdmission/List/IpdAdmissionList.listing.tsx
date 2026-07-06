import React, {FC} from 'react'
import {Tag} from 'antd'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {IpdAdmissionAction} from '../Actions/IpdAdmission.actions'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'

const statusColor = (status: string): string => {
  switch (status) {
    case 'admitted':
      return 'blue'
    case 'discharged':
      return 'green'
    case 'dama':
      return 'gold'
    case 'deceased':
      return 'default'
    default:
      return 'default'
  }
}

const IpdAdmissionListing: FC<any> = (props) => {
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
      dataIndex: 'admission_no',
      key: 'admission_no',
      title: t('Admission No'),
      sorter: true,
      width: '15%',
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={IpdAdmissionAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
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
    {
      dataIndex: 'ward_name',
      key: 'ward_name',
      title: t('Ward / Bed'),
      width: '15%',
      render: (_: any, record: any) => `${record.ward_name || '-'} / ${record.bed_number || '-'}`,
    },
    {
      dataIndex: 'admission_type',
      key: 'admission_type',
      title: t('Type'),
      width: '10%',
      render: (v: string) => <span className='text-capitalize'>{v}</span>,
    },
    {
      dataIndex: 'admission_date',
      key: 'admission_date',
      title: t('Admission Date'),
      sorter: true,
      width: '15%',
      render: (value: any) => DateTimeUtils.formatDateTimeA(value),
    },
    {
      dataIndex: 'admission_status',
      key: 'admission_status',
      title: t('Status'),
      width: '10%',
      render: (v: string) => (
        <Tag color={statusColor(v)} className='text-capitalize'>
          {v}
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
        <ListItemAction
          entityId={record.id}
          actionList={IpdAdmissionAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:ipd-admission:multiSelect'
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

export default React.memo(IpdAdmissionListing)

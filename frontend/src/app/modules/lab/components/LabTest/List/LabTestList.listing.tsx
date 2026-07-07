import React, {FC} from 'react'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {LabTestAction} from '../Actions/LabTest.actions'
import {useLang} from 'src/app/hooks/useLang'

const LabTestListing: FC<any> = (props) => {
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
      width: '12%',
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={LabTestAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-bolder cursor-pointer'>{text}</span>
        </ViewAction>
      ),
    },
    {
      dataIndex: 'name',
      key: 'name',
      title: t('Name'),
      width: '28%',
    },
    {
      dataIndex: 'category',
      key: 'category',
      title: t('Category'),
      width: '15%',
    },
    {
      dataIndex: 'sample_type',
      key: 'sample_type',
      title: t('Sample Type'),
      width: '15%',
    },
    {
      dataIndex: 'tat_hours',
      key: 'tat_hours',
      title: t('TAT (hrs)'),
      width: '10%',
    },
    {
      dataIndex: 'default_price',
      key: 'default_price',
      title: t('Price'),
      width: '10%',
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
          actionList={LabTestAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:lab-test:multiSelect'
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

export default React.memo(LabTestListing)

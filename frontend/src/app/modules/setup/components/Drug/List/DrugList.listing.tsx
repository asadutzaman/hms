import React, {FC} from 'react'
import {Tag} from 'antd'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {DrugAction} from '../Actions/Drug.actions'
import {useLang} from 'src/app/hooks/useLang'

const DrugListing: FC<any> = (props) => {
  const {loading, listData, selectedRowKeys, handleOnChanged, handleTableChange, handleCallbackFunc} = props
  const {t} = useLang()

  const columns = [
    {
      dataIndex: 'generic_name',
      key: 'generic_name',
      title: t('Generic Name'),
      sorter: true,
      width: '18%',
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={DrugAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-bolder cursor-pointer'>{`[${record.code}] ${text}`}</span>
        </ViewAction>
      ),
    },
    {
      dataIndex: 'brand_name',
      key: 'brand_name',
      title: t('Brand Name'),
      width: '14%',
      render: (v: string, record: any) =>
        v ? (
          <span>
            {v} {record.is_generic && <Tag color='blue'>{t('Generic')}</Tag>}
          </span>
        ) : (
          record.is_generic && <Tag color='blue'>{t('Generic')}</Tag>
        ),
    },
    {dataIndex: 'strength', key: 'strength', title: t('Strength'), width: '8%', render: (v: string) => v || '-'},
    {
      dataIndex: 'dosage_form',
      key: 'dosage_form',
      title: t('Form'),
      width: '10%',
      render: (v: string) => <span className='text-capitalize'>{v}</span>,
    },
    {dataIndex: 'item_category_name', key: 'item_category_name', title: t('Category'), width: '10%'},
    {
      dataIndex: 'is_controlled',
      key: 'is_controlled',
      title: t('Controlled'),
      width: '8%',
      render: (v: boolean) => (v ? <Tag color='volcano'>{t('Yes')}</Tag> : '-'),
    },
    {dataIndex: 'reorder_qty', key: 'reorder_qty', title: t('Reorder Qty'), width: '8%'},
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      width: '10%',
      align: 'center',
      render: (_text: string, record: any) => (
        <ListItemAction
          entityId={record.id}
          actionList={DrugAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:drug:multiSelect'
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

export default React.memo(DrugListing)

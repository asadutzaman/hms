import React, {FC} from 'react'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {RateContractAction} from '../Actions/RateContract.actions'
import {Tag} from 'antd'
import {useLang} from 'src/app/hooks/useLang'

const statusColor: Record<string, string> = {
  active: 'green',
  pending_approval: 'gold',
  expired: 'default',
  cancelled: 'red',
}

const RateContractListing: FC<any> = (props) => {
  const {loading, listData, selectedRowKeys, handleOnChanged, handleTableChange, handleCallbackFunc} = props
  const {t} = useLang()

  const columns = [
    {
      dataIndex: 'item_name',
      key: 'item_name',
      title: t('Item'),
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={RateContractAction.COMMON_ACTION.VIEW}
          defaultViewText={`[${record.item_code}] ${text}`}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-bolder cursor-pointer'>
            [{record.item_code}] {text}
          </span>
        </ViewAction>
      ),
    },
    {dataIndex: 'supplier_name', key: 'supplier_name', title: t('Supplier')},
    {dataIndex: 'contract_price', key: 'contract_price', title: t('Contract Price')},
    {dataIndex: 'valid_from', key: 'valid_from', title: t('Valid From')},
    {dataIndex: 'valid_to', key: 'valid_to', title: t('Valid To')},
    {
      dataIndex: 'contract_status',
      key: 'contract_status',
      title: t('Status'),
      render: (text: string) => <Tag color={statusColor[text] || 'default'}>{text?.toUpperCase()}</Tag>,
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      width: '10%',
      align: 'center',
      render: (text: string, record: any) => (
        <ListItemAction
          entity={record}
          entityId={record.id}
          actionList={RateContractAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:rateContract:multiSelect'
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

export default React.memo(RateContractListing)

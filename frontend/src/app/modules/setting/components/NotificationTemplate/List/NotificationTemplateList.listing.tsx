import React, {FC} from 'react'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {Tag} from 'antd'
import {NotificationTemplateAction} from '../Actions/NotificationTemplate.actions'
import {useLang} from 'src/app/hooks/useLang'

const channelColor: Record<string, string> = {
  in_app: 'blue',
  email: 'purple',
  sms: 'green',
}

const NotificationTemplateListing: FC<any> = (props) => {
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
      dataIndex: 'key',
      key: 'key',
      title: t('Key'),
      width: '25%',
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={NotificationTemplateAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-bolder cursor-pointer'>{text}</span>
        </ViewAction>
      ),
    },
    {dataIndex: 'name', key: 'name', title: t('Name'), width: '25%'},
    {
      dataIndex: 'channel',
      key: 'channel',
      title: t('Channel'),
      width: '15%',
      render: (v: string) => <Tag color={channelColor[v] || 'default'}>{v}</Tag>,
    },
    {
      dataIndex: 'is_active',
      key: 'is_active',
      title: t('Active'),
      width: '10%',
      render: (v: any) => (v ? t('Yes') : t('No')),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      width: '15%',
      align: 'center',
      render: (text: string, record: any) => (
        <ListItemAction
          entityId={record.id}
          actionList={NotificationTemplateAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:notification-template:multiSelect'
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

export default React.memo(NotificationTemplateListing)

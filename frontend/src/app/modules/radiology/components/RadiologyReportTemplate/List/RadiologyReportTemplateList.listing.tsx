import React, {FC} from 'react'
import ListItemAction from 'src/app/components/Actions/ListItemAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AntTable from 'src/app/components/Table/AntTable'
import {RadiologyReportTemplateAction} from '../Actions/RadiologyReportTemplate.actions'
import {useLang} from 'src/app/hooks/useLang'

const RadiologyReportTemplateListing: FC<any> = (props) => {
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
      dataIndex: 'name',
      key: 'name',
      title: t('Name'),
      width: '35%',
      render: (text: string, record: any) => (
        <ViewAction
          entityId={record.id}
          actionItem={RadiologyReportTemplateAction.COMMON_ACTION.VIEW}
          defaultViewText={text}
          handleCallbackFunc={handleCallbackFunc}
        >
          <span className='grid-row-view-action fw-bolder cursor-pointer'>{text}</span>
        </ViewAction>
      ),
    },
    {dataIndex: 'modality', key: 'modality', title: t('Modality'), width: '20%', render: (v: string) => (v || '').toUpperCase()},
    {dataIndex: 'body_part', key: 'body_part', title: t('Body Part'), width: '25%'},
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      width: '20%',
      align: 'center',
      render: (text: string, record: any) => (
        <ListItemAction
          entityId={record.id}
          actionList={RadiologyReportTemplateAction.LIST_ITEM_ACTION}
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
        rowSelectionPermission='auth:radiology-report-template:multiSelect'
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

export default React.memo(RadiologyReportTemplateListing)

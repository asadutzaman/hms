import React, {FC, useState} from 'react'
import {Descriptions, Tag, Button, Space} from 'antd'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {RateContractAction} from '../Actions/RateContract.actions'
import {RateContractApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'

const statusColor: Record<string, string> = {
  active: 'green',
  pending_approval: 'gold',
  expired: 'default',
  cancelled: 'red',
}

const RateContractView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  const [working, setWorking] = useState(false)

  const runAction = (action: 'submit' | 'approve' | 'reject') => {
    setWorking(true)
    const apiCall =
      action === 'submit'
        ? RateContractApi.submit(itemData.id)
        : action === 'approve'
        ? RateContractApi.approve(itemData.id)
        : RateContractApi.reject(itemData.id)

    apiCall
      .then(() => {
        Message.success('Updated successfully')
        handleCallbackFunc('singleAction', 'reloadView')
        handleCallbackFunc('singleAction', 'reloadListing')
      })
      .catch(() => Message.error('Action failed'))
      .finally(() => setWorking(false))
  }

  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12 d-flex justify-content-between align-items-center'>
          <Tag color={statusColor[itemData.contract_status] || 'default'} style={{fontSize: 14}}>
            {itemData.contract_status?.toUpperCase()}
          </Tag>
          <div>
            <EditAction
              entityId={itemData.id}
              actionItem={RateContractAction.COMMON_ACTION.EDIT}
              handleCallbackFunc={handleCallbackFunc}
            />
            <DeleteAction
              entityId={itemData.id}
              actionItem={RateContractAction.COMMON_ACTION.DELETE}
              handleCallbackFunc={handleCallbackFunc}
            />
          </div>
        </div>
      </div>

      <Descriptions bordered column={2} size='small' className='mb-6'>
        <Descriptions.Item label={t('Item')}>
          [{itemData.item_code}] {itemData.item_name}
        </Descriptions.Item>
        <Descriptions.Item label={t('Supplier')}>{itemData.supplier_name}</Descriptions.Item>
        <Descriptions.Item label={t('Contract Price')}>{itemData.contract_price}</Descriptions.Item>
        <Descriptions.Item label={t('Approval Status')}>{itemData.process_status}</Descriptions.Item>
        <Descriptions.Item label={t('Valid From')}>{itemData.valid_from}</Descriptions.Item>
        <Descriptions.Item label={t('Valid To')}>{itemData.valid_to}</Descriptions.Item>
        <Descriptions.Item label={t('Approved At')} span={2}>
          {itemData.approved_at || '-'}
        </Descriptions.Item>
      </Descriptions>

      {itemData.process_status === 'DRAFT' && (
        <Space>
          <Button type='primary' loading={working} onClick={() => runAction('submit')}>
            {t('Submit for Approval')}
          </Button>
        </Space>
      )}

      {itemData.process_status === 'SUBMITTED' && (
        <Space>
          <Button type='primary' loading={working} onClick={() => runAction('approve')}>
            {t('Approve')}
          </Button>
          <Button danger loading={working} onClick={() => runAction('reject')}>
            {t('Reject')}
          </Button>
        </Space>
      )}
    </div>
  )
}
export default React.memo(RateContractView)

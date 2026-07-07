import React, {FC, useEffect, useState} from 'react'
import {Button, Select, Tag} from 'antd'
import {ReloadOutlined, PlusOutlined} from '@ant-design/icons'
import AntTable from 'src/app/components/Table/AntTable'
import {RadiologyOrderApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {DateTimeUtils} from 'src/app/utils'
import PlaceRadiologyOrderModal from './PlaceRadiologyOrderModal'
import RadiologyOrderViewController from './View/RadiologyOrderView.controller'

const {Option} = Select

const STATUS_COLOR: Record<string, string> = {
  ordered: 'default',
  in_progress: 'gold',
  reported: 'green',
  cancelled: 'red',
}

const PRIORITY_COLOR: Record<string, string> = {
  routine: 'default',
  urgent: 'orange',
  stat: 'red',
}

const RadiologyOrderWorklistController: FC = () => {
  const {t} = useLang()
  const {handleErrorMessage} = useErrorHandler()
  const [loading, setLoading] = useState(false)
  const [orders, setOrders] = useState<any[]>([])
  const [statusFilter, setStatusFilter] = useState<string>('')
  const [showPlaceOrder, setShowPlaceOrder] = useState(false)
  const [entityId, setEntityId] = useState<any>(null)
  const [isShowView, setIsShowView] = useState(false)
  const [reloadView, setReloadView] = useState(0)

  const loadData = () => {
    setLoading(true)
    RadiologyOrderApi.worklist()
      .then((res: any) => setOrders(res?.data?.data ?? res?.data ?? []))
      .catch((err) => handleErrorMessage(err?.response || err))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const handleCallbackFunc = (event: any, action: string, recordId?: any) => {
    if (action === 'hideView') {
      setIsShowView(false)
    } else if (action === 'reloadView') {
      setReloadView((v) => v + 1)
      loadData()
    } else if (action === 'reloadListing') {
      loadData()
    }
  }

  const openView = (id: any) => {
    setEntityId(id)
    setIsShowView(true)
  }

  const filteredOrders = statusFilter ? orders.filter((o) => o.order_status === statusFilter) : orders

  const columns = [
    {
      dataIndex: 'rad_order_no',
      key: 'rad_order_no',
      title: t('Order No'),
      width: '15%',
      render: (text: string, record: any) => (
        <span className='fw-bolder cursor-pointer text-primary' onClick={() => openView(record.id)}>
          {text}
        </span>
      ),
    },
    {
      dataIndex: 'patient_name',
      key: 'patient_name',
      title: t('Patient'),
      width: '20%',
      render: (text: string, record: any) => (
        <div>
          <div>{text}</div>
          <div className='text-muted fs-8'>MRN {record.mrn}</div>
        </div>
      ),
    },
    {
      dataIndex: 'priority',
      key: 'priority',
      title: t('Priority'),
      width: '10%',
      render: (value: string) => <Tag color={PRIORITY_COLOR[value] || 'default'}>{(value || '').toUpperCase()}</Tag>,
    },
    {
      dataIndex: 'order_status_label',
      key: 'order_status',
      title: t('Status'),
      width: '15%',
      render: (text: string, record: any) => <Tag color={STATUS_COLOR[record.order_status] || 'default'}>{text}</Tag>,
    },
    {
      dataIndex: 'ordered_at',
      key: 'ordered_at',
      title: t('Ordered At'),
      width: '20%',
      render: (value: any) => DateTimeUtils.formatDateTimeA(value),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      width: '10%',
      align: 'center',
      render: (text: string, record: any) => (
        <Button size='small' onClick={() => openView(record.id)}>
          {t('View')}
        </Button>
      ),
    },
  ]

  return (
    <div className='card p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <h3 className='mb-0'>{t('Radiology Order Worklist')}</h3>
        <div>
          <Select
            allowClear
            placeholder={t('Filter by status')}
            style={{width: 200}}
            className='me-3'
            value={statusFilter || undefined}
            onChange={(v) => setStatusFilter(v || '')}
          >
            <Option value='ordered'>{t('Ordered')}</Option>
            <Option value='in_progress'>{t('In Progress')}</Option>
            <Option value='reported'>{t('Reported')}</Option>
            <Option value='cancelled'>{t('Cancelled')}</Option>
          </Select>
          <Button icon={<ReloadOutlined />} onClick={loadData} className='me-3'>
            {t('Refresh')}
          </Button>
          <Button type='primary' icon={<PlusOutlined />} onClick={() => setShowPlaceOrder(true)}>
            {t('New Radiology Order')}
          </Button>
        </div>
      </div>

      <AntTable
        className='table-layout'
        rowSelection={false}
        dataSource={filteredOrders}
        columns={columns}
        loading={loading}
        handleOnChanged={() => {}}
      />

      <PlaceRadiologyOrderModal visible={showPlaceOrder} onClose={() => setShowPlaceOrder(false)} onCreated={loadData} />

      <RadiologyOrderViewController
        entityId={entityId}
        reloadView={reloadView}
        isShowView={isShowView}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default RadiologyOrderWorklistController

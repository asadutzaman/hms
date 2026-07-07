import React, {FC, useEffect, useState} from 'react'
import {Button, Table, Tag} from 'antd'
import {PlusOutlined, ReloadOutlined} from '@ant-design/icons'
import {LabOrderApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {DateTimeUtils} from 'src/app/utils'
import PlaceLabOrderModal from 'src/app/modules/lab/components/LabOrder/PlaceLabOrderModal'
import LabOrderViewController from 'src/app/modules/lab/components/LabOrder/View/LabOrderView.controller'

const STATUS_COLOR: Record<string, string> = {
  ordered: 'default',
  sample_collected: 'blue',
  in_progress: 'gold',
  verified: 'cyan',
  reported: 'green',
  cancelled: 'red',
}

interface LabOrdersPanelProps {
  patientId: number
  patientLabel?: string
  opdVisitId?: number
  ipdAdmissionId?: number
}

const LabOrdersPanel: FC<LabOrdersPanelProps> = ({patientId, patientLabel, opdVisitId, ipdAdmissionId}) => {
  const {t} = useLang()
  const {handleErrorMessage} = useErrorHandler()
  const [loading, setLoading] = useState(false)
  const [orders, setOrders] = useState<any[]>([])
  const [showPlaceOrder, setShowPlaceOrder] = useState(false)
  const [entityId, setEntityId] = useState<any>(null)
  const [isShowView, setIsShowView] = useState(false)
  const [reloadView, setReloadView] = useState(0)

  const loadData = () => {
    setLoading(true)
    const request = ipdAdmissionId
      ? LabOrderApi.byIpdAdmission(ipdAdmissionId)
      : opdVisitId
      ? LabOrderApi.byOpdVisit(opdVisitId)
      : LabOrderApi.byPatient(patientId)

    request
      .then((res: any) => setOrders(res?.data?.data ?? res?.data ?? []))
      .catch((err) => handleErrorMessage(err?.response || err))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [patientId, opdVisitId, ipdAdmissionId])

  const handleCallbackFunc = (event: any, action: string) => {
    if (action === 'hideView') {
      setIsShowView(false)
    } else if (action === 'reloadView') {
      setReloadView((v) => v + 1)
      loadData()
    }
  }

  const openView = (id: any) => {
    setEntityId(id)
    setIsShowView(true)
  }

  const columns = [
    {
      dataIndex: 'lab_order_no',
      key: 'lab_order_no',
      title: t('Order No'),
      render: (text: string, record: any) => (
        <span className='fw-bolder cursor-pointer text-primary' onClick={() => openView(record.id)}>
          {text}
        </span>
      ),
    },
    {
      dataIndex: 'order_status_label',
      key: 'order_status',
      title: t('Status'),
      render: (text: string, record: any) => <Tag color={STATUS_COLOR[record.order_status] || 'default'}>{text}</Tag>,
    },
    {dataIndex: 'priority', key: 'priority', title: t('Priority'), render: (v: string) => (v || '').toUpperCase()},
    {
      dataIndex: 'ordered_at',
      key: 'ordered_at',
      title: t('Ordered At'),
      render: (v: any) => DateTimeUtils.formatDateTimeA(v),
    },
  ]

  return (
    <div className='p-4'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <h5 className='mb-0'>{t('Lab Orders')}</h5>
        <div>
          <Button icon={<ReloadOutlined />} onClick={loadData} className='me-2' size='small'>
            {t('Refresh')}
          </Button>
          <Button type='primary' size='small' icon={<PlusOutlined />} onClick={() => setShowPlaceOrder(true)}>
            {t('Place Lab Order')}
          </Button>
        </div>
      </div>

      <Table rowKey='id' size='small' columns={columns} dataSource={orders} loading={loading} pagination={false} />

      <PlaceLabOrderModal
        visible={showPlaceOrder}
        onClose={() => setShowPlaceOrder(false)}
        onCreated={loadData}
        defaultPatientId={patientId}
        defaultPatientLabel={patientLabel}
        lockPatient
        opdVisitId={opdVisitId}
        ipdAdmissionId={ipdAdmissionId}
      />

      <LabOrderViewController
        entityId={entityId}
        reloadView={reloadView}
        isShowView={isShowView}
        handleCallbackFunc={handleCallbackFunc}
      />
    </div>
  )
}

export default LabOrdersPanel

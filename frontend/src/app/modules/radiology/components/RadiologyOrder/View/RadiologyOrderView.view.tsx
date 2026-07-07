import React, {FC, useState} from 'react'
import {Tag, Button, Table, notification, Popconfirm} from 'antd'
import {DateTimeUtils} from 'src/app/utils'
import {RadiologyOrderApi, RadiologyReportApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import EnterReportModal from './EnterReportModal'

const ORDER_STATUS_COLOR: Record<string, string> = {
  ordered: 'default',
  in_progress: 'gold',
  reported: 'green',
  cancelled: 'red',
}

const ITEM_STATUS_COLOR: Record<string, string> = {
  ordered: 'default',
  in_progress: 'gold',
  reported: 'purple',
  verified: 'green',
  cancelled: 'red',
}

const RadiologyOrderView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  const [busy, setBusy] = useState(false)
  const [enterReportFor, setEnterReportFor] = useState<any>(null)

  const reload = () => handleCallbackFunc(null, 'reloadView')

  const isTerminal = itemData.order_status === 'cancelled'

  const handleCancelOrder = async () => {
    setBusy(true)
    try {
      await RadiologyOrderApi.cancel(itemData.id)
      notification.success({message: t('Order cancelled')})
      reload()
    } catch (e: any) {
      notification.error({message: t('Failed to cancel order'), description: e?.response?.data?.message})
    } finally {
      setBusy(false)
    }
  }

  const handleVerify = async (orderItemId: any) => {
    setBusy(true)
    try {
      await RadiologyReportApi.verify(orderItemId)
      notification.success({message: t('Report verified')})
      reload()
    } catch (e: any) {
      notification.error({message: t('Failed to verify report'), description: e?.response?.data?.message})
    } finally {
      setBusy(false)
    }
  }

  const handleDownloadPdf = async () => {
    try {
      const res: any = await RadiologyOrderApi.reportPdf(itemData.id)
      const blob = new Blob([res.data], {type: 'application/pdf'})
      const url = window.URL.createObjectURL(blob)
      window.open(url, '_blank')
    } catch (e: any) {
      notification.error({message: t('Failed to generate report PDF')})
    }
  }

  const itemColumns = [
    {dataIndex: 'test_name_snapshot', key: 'test_name_snapshot', title: t('Study')},
    {dataIndex: 'modality_snapshot', key: 'modality_snapshot', title: t('Modality'), render: (v: string) => (v || '').toUpperCase()},
    {
      dataIndex: 'item_status_label',
      key: 'item_status',
      title: t('Status'),
      render: (text: string, record: any) => <Tag color={ITEM_STATUS_COLOR[record.item_status] || 'default'}>{text}</Tag>,
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      render: (_: any, record: any) => (
        <>
          {record.item_status !== 'cancelled' && record.item_status !== 'verified' && (
            <Button size='small' className='me-2' onClick={() => setEnterReportFor(record)}>
              {t('Enter Report')}
            </Button>
          )}
          {record.item_status === 'reported' && (
            <Button size='small' type='primary' onClick={() => handleVerify(record.id)}>
              {t('Verify')}
            </Button>
          )}
        </>
      ),
    },
  ]

  const renderReportDetail = (item: any) => {
    if (!item.report || (!item.report.findings && !item.report.impression)) {
      return <div className='text-muted fs-8 ps-4'>{t('No report entered yet.')}</div>
    }
    return (
      <div className='ps-4' style={{width: '90%'}}>
        <div className='mb-2'>
          <span className='fw-bold'>{t('Findings')}: </span>
          {item.report.findings || '-'}
        </div>
        <div>
          <span className='fw-bold'>{t('Impression')}: </span>
          {item.report.impression || '-'}
        </div>
      </div>
    )
  }

  return (
    <div className='card card-body position-relative'>
      <div className='d-flex justify-content-between align-items-start mb-5'>
        <div>
          <h3 className='mb-1'>{itemData.rad_order_no}</h3>
          <div className='text-muted'>
            {itemData.patient_name} &nbsp;(MRN {itemData.mrn})
          </div>
        </div>
        <div className='text-end'>
          <Tag color={ORDER_STATUS_COLOR[itemData.order_status] || 'default'} className='fs-6 mb-2'>
            {itemData.order_status_label}
          </Tag>
          <div className='text-muted fs-8'>{DateTimeUtils.formatDateTimeA(itemData.ordered_at)}</div>
        </div>
      </div>

      <div className='mb-5'>
        <span className='fw-bold'>{t('Priority')}: </span>
        {(itemData.priority || '').toUpperCase()}
        {itemData.clinical_indication && (
          <div className='mt-2'>
            <span className='fw-bold'>{t('Clinical Indication')}: </span>
            {itemData.clinical_indication}
          </div>
        )}
      </div>

      <div className='d-flex mb-6'>
        {!isTerminal && (
          <Popconfirm title={t('Cancel this radiology order?')} onConfirm={handleCancelOrder}>
            <Button danger className='me-3' loading={busy}>
              {t('Cancel Order')}
            </Button>
          </Popconfirm>
        )}
        {['in_progress', 'reported'].includes(itemData.order_status) && (
          <Button className='me-3' onClick={handleDownloadPdf}>
            {t('Download Report PDF')}
          </Button>
        )}
      </div>

      <h5>{t('Studies')}</h5>
      <Table
        rowKey='id'
        columns={itemColumns}
        dataSource={itemData.items || []}
        pagination={false}
        expandable={{
          expandedRowRender: (record: any) => renderReportDetail(record),
          defaultExpandAllRows: true,
        }}
      />

      <EnterReportModal
        visible={!!enterReportFor}
        orderItem={enterReportFor}
        onClose={() => setEnterReportFor(null)}
        onSaved={reload}
      />
    </div>
  )
}
export default React.memo(RadiologyOrderView)

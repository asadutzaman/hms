import React, {FC, useState} from 'react'
import {Tag, Button, Input, Table, notification, Popconfirm, Select} from 'antd'
import {DateTimeUtils} from 'src/app/utils'
import {LabOrderApi, LabSampleApi, LabResultApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import EnterResultsModal from './EnterResultsModal'

const {Option} = Select

const ORDER_STATUS_COLOR: Record<string, string> = {
  ordered: 'default',
  sample_collected: 'blue',
  in_progress: 'gold',
  verified: 'cyan',
  reported: 'green',
  cancelled: 'red',
}

const SAMPLE_STATUS_COLOR: Record<string, string> = {
  pending_collection: 'default',
  collected: 'blue',
  received: 'gold',
  rejected: 'red',
}

const ITEM_STATUS_COLOR: Record<string, string> = {
  ordered: 'default',
  sample_collected: 'blue',
  in_progress: 'gold',
  entered: 'purple',
  verified: 'green',
  cancelled: 'red',
}

const FLAG_STYLE: Record<string, {color: string; label: string}> = {
  normal: {color: 'inherit', label: ''},
  low: {color: '#d46b08', label: 'LOW'},
  high: {color: '#d46b08', label: 'HIGH'},
  critical_low: {color: '#cf1322', label: 'CRITICAL LOW'},
  critical_high: {color: '#cf1322', label: 'CRITICAL HIGH'},
}

const LabOrderView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  const [sampleType, setSampleType] = useState('')
  const [barcodeInput, setBarcodeInput] = useState('')
  const [busy, setBusy] = useState(false)
  const [enterResultsFor, setEnterResultsFor] = useState<any>(null)

  const reload = () => handleCallbackFunc(null, 'reloadView')

  const isTerminal = itemData.order_status === 'reported' || itemData.order_status === 'cancelled'

  const handleCollectSample = async () => {
    if (!sampleType) {
      notification.warning({message: t('Enter a sample type')})
      return
    }
    setBusy(true)
    try {
      await LabSampleApi.collect({lab_order_id: itemData.id, sample_type: sampleType})
      notification.success({message: t('Sample collected')})
      setSampleType('')
      reload()
    } catch (e: any) {
      notification.error({message: t('Failed to collect sample'), description: e?.response?.data?.message})
    } finally {
      setBusy(false)
    }
  }

  const handleReceiveByBarcode = async () => {
    if (!barcodeInput) return
    setBusy(true)
    try {
      await LabSampleApi.receiveByBarcode({barcode: barcodeInput})
      notification.success({message: t('Sample received')})
      setBarcodeInput('')
      reload()
    } catch (e: any) {
      notification.error({message: t('Failed to receive sample'), description: e?.response?.data?.message})
    } finally {
      setBusy(false)
    }
  }

  const handleRejectSample = async (sampleId: any) => {
    setBusy(true)
    try {
      await LabSampleApi.reject(sampleId, {reason: 'Rejected by lab technician'})
      notification.success({message: t('Sample rejected')})
      reload()
    } catch (e: any) {
      notification.error({message: t('Failed to reject sample'), description: e?.response?.data?.message})
    } finally {
      setBusy(false)
    }
  }

  const handleVerify = async (orderItemId: any) => {
    setBusy(true)
    try {
      await LabResultApi.verify(orderItemId)
      notification.success({message: t('Results verified')})
      reload()
    } catch (e: any) {
      notification.error({message: t('Failed to verify results'), description: e?.response?.data?.message})
    } finally {
      setBusy(false)
    }
  }

  const handleCancelOrder = async () => {
    setBusy(true)
    try {
      await LabOrderApi.cancel(itemData.id)
      notification.success({message: t('Order cancelled')})
      reload()
    } catch (e: any) {
      notification.error({message: t('Failed to cancel order'), description: e?.response?.data?.message})
    } finally {
      setBusy(false)
    }
  }

  const handleMarkReported = async () => {
    setBusy(true)
    try {
      await LabOrderApi.markReported(itemData.id)
      notification.success({message: t('Order marked as reported')})
      reload()
    } catch (e: any) {
      notification.error({message: t('Failed to mark reported'), description: e?.response?.data?.message})
    } finally {
      setBusy(false)
    }
  }

  const handleDownloadPdf = async () => {
    try {
      const res: any = await LabOrderApi.reportPdf(itemData.id)
      const blob = new Blob([res.data], {type: 'application/pdf'})
      const url = window.URL.createObjectURL(blob)
      window.open(url, '_blank')
    } catch (e: any) {
      notification.error({message: t('Failed to generate report PDF')})
    }
  }

  const sampleColumns = [
    {dataIndex: 'barcode', key: 'barcode', title: t('Barcode')},
    {dataIndex: 'sample_type', key: 'sample_type', title: t('Sample Type')},
    {
      dataIndex: 'sample_status',
      key: 'sample_status',
      title: t('Status'),
      render: (v: string) => <Tag color={SAMPLE_STATUS_COLOR[v] || 'default'}>{v}</Tag>,
    },
    {
      dataIndex: 'collected_at',
      key: 'collected_at',
      title: t('Collected At'),
      render: (v: any) => (v ? DateTimeUtils.formatDateTimeA(v) : '-'),
    },
    {
      dataIndex: 'received_at',
      key: 'received_at',
      title: t('Received At'),
      render: (v: any) => (v ? DateTimeUtils.formatDateTimeA(v) : '-'),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      render: (_: any, record: any) =>
        record.sample_status !== 'rejected' && record.sample_status !== 'received' ? (
          <Popconfirm title={t('Reject this sample?')} onConfirm={() => handleRejectSample(record.id)}>
            <Button size='small' danger>
              {t('Reject')}
            </Button>
          </Popconfirm>
        ) : null,
    },
  ]

  const renderResultsTable = (item: any) => {
    const results = item.results || []
    if (results.length === 0) {
      return <div className='text-muted fs-8 ps-4'>{t('No results entered yet.')}</div>
    }
    return (
      <table className='table table-sm table-row-dashed align-middle gs-0 gy-2 ms-4' style={{width: '95%'}}>
        <thead>
          <tr>
            <th>{t('Parameter')}</th>
            <th>{t('Result')}</th>
            <th>{t('Unit')}</th>
            <th>{t('Reference Range')}</th>
            <th>{t('Flag')}</th>
          </tr>
        </thead>
        <tbody>
          {results.map((r: any) => {
            const flagMeta = FLAG_STYLE[r.result_flag] || FLAG_STYLE.normal
            return (
              <tr key={r.id}>
                <td>{r.parameter_name_snapshot}</td>
                <td style={{color: flagMeta.color, fontWeight: r.is_critical ? 700 : 400}}>{r.result_value}</td>
                <td>{r.unit_snapshot}</td>
                <td>{r.reference_range_display}</td>
                <td style={{color: flagMeta.color, fontWeight: r.is_critical ? 700 : 400}}>{flagMeta.label}</td>
              </tr>
            )
          })}
        </tbody>
      </table>
    )
  }

  const itemColumns = [
    {dataIndex: 'test_name_snapshot', key: 'test_name_snapshot', title: t('Test')},
    {dataIndex: 'sample_type_snapshot', key: 'sample_type_snapshot', title: t('Sample')},
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
          {['sample_collected', 'in_progress', 'entered'].includes(record.item_status) && (
            <Button size='small' className='me-2' onClick={() => setEnterResultsFor(record)}>
              {t('Enter Results')}
            </Button>
          )}
          {record.item_status === 'entered' && (
            <Button size='small' type='primary' onClick={() => handleVerify(record.id)}>
              {t('Verify')}
            </Button>
          )}
        </>
      ),
    },
  ]

  return (
    <div className='card card-body position-relative'>
      <div className='d-flex justify-content-between align-items-start mb-5'>
        <div>
          <h3 className='mb-1'>{itemData.lab_order_no}</h3>
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
        {!isTerminal && itemData.order_status !== 'cancelled' && (
          <Popconfirm title={t('Cancel this lab order?')} onConfirm={handleCancelOrder}>
            <Button danger className='me-3' loading={busy}>
              {t('Cancel Order')}
            </Button>
          </Popconfirm>
        )}
        {itemData.order_status === 'verified' && (
          <Button type='primary' className='me-3' loading={busy} onClick={handleMarkReported}>
            {t('Mark Reported')}
          </Button>
        )}
        {['verified', 'reported'].includes(itemData.order_status) && (
          <Button className='me-3' onClick={handleDownloadPdf}>
            {t('Download Report PDF')}
          </Button>
        )}
      </div>

      {!isTerminal && (
        <div className='card card-body bg-light mb-6'>
          <div className='row g-3 align-items-end'>
            <div className='col-md-4'>
              <label className='form-label'>{t('Collect New Sample')}</label>
              <Input
                placeholder={t('Sample type e.g. Serum')}
                value={sampleType}
                onChange={(e) => setSampleType(e.target.value)}
              />
            </div>
            <div className='col-md-2'>
              <Button onClick={handleCollectSample} loading={busy}>
                {t('Collect')}
              </Button>
            </div>
            <div className='col-md-4'>
              <label className='form-label'>{t('Receive by Barcode')}</label>
              <Input
                placeholder={t('Scan/enter barcode')}
                value={barcodeInput}
                onChange={(e) => setBarcodeInput(e.target.value)}
              />
            </div>
            <div className='col-md-2'>
              <Button onClick={handleReceiveByBarcode} loading={busy}>
                {t('Receive')}
              </Button>
            </div>
          </div>
        </div>
      )}

      <h5>{t('Samples')}</h5>
      <Table
        className='mb-6'
        size='small'
        rowKey='id'
        columns={sampleColumns}
        dataSource={itemData.samples || []}
        pagination={false}
      />

      <h5>{t('Test Items')}</h5>
      <Table
        rowKey='id'
        columns={itemColumns}
        dataSource={itemData.items || []}
        pagination={false}
        expandable={{
          expandedRowRender: (record: any) => renderResultsTable(record),
          defaultExpandAllRows: true,
        }}
      />

      <EnterResultsModal
        visible={!!enterResultsFor}
        orderItem={enterResultsFor}
        onClose={() => setEnterResultsFor(null)}
        onSaved={reload}
      />
    </div>
  )
}
export default React.memo(LabOrderView)

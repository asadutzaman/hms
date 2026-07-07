import React, {FC, useEffect, useState} from 'react'
import {Button, Select, Tag} from 'antd'
import {ReloadOutlined, PlusOutlined} from '@ant-design/icons'
import AntTable from 'src/app/components/Table/AntTable'
import {PreAuthorizationApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {DateTimeUtils} from 'src/app/utils'
import SubmitPreAuthModal from './SubmitPreAuthModal'
import PreAuthorizationDetailModal from './PreAuthorizationDetailModal'

const {Option} = Select

const STATUS_COLOR: Record<string, string> = {
  submitted: 'default',
  under_review: 'gold',
  approved: 'green',
  rejected: 'red',
  expired: 'default',
  cancelled: 'default',
}

const PreAuthorizationWorklistController: FC = () => {
  const {t} = useLang()
  const {handleErrorMessage} = useErrorHandler()
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [pendingOnly, setPendingOnly] = useState(true)
  const [showSubmit, setShowSubmit] = useState(false)
  const [selectedRecord, setSelectedRecord] = useState<any>(null)

  const loadData = () => {
    setLoading(true)
    const request = pendingOnly ? PreAuthorizationApi.pending() : PreAuthorizationApi.list({$top: 100})
    request
      .then((res: any) => setRows(res?.data?.data ?? res?.data?.results ?? res?.data ?? []))
      .catch((err: any) => handleErrorMessage(err?.response || err))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pendingOnly])

  const columns = [
    {dataIndex: 'pa_no', key: 'pa_no', title: t('PA No')},
    {
      dataIndex: 'patient_name',
      key: 'patient_name',
      title: t('Patient'),
      render: (text: string, record: any) => (
        <div>
          <div>{text}</div>
          <div className='text-muted fs-8'>MRN {record.mrn}</div>
        </div>
      ),
    },
    {dataIndex: 'insurance_company_name', key: 'insurance_company_name', title: t('Insurer')},
    {dataIndex: 'estimated_amount', key: 'estimated_amount', title: t('Estimated Amount')},
    {
      dataIndex: 'pa_status_label',
      key: 'pa_status',
      title: t('Status'),
      render: (text: string, record: any) => <Tag color={STATUS_COLOR[record.pa_status] || 'default'}>{text}</Tag>,
    },
    {
      dataIndex: 'requested_at',
      key: 'requested_at',
      title: t('Requested At'),
      render: (v: any) => DateTimeUtils.formatDateTimeA(v),
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      render: (_: any, record: any) => (
        <Button size='small' onClick={() => setSelectedRecord(record)}>
          {t('View')}
        </Button>
      ),
    },
  ]

  return (
    <div className='card p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <h3 className='mb-0'>{t('Pre-Authorization Requests')}</h3>
        <div>
          <Select
            style={{width: 200}}
            className='me-3'
            value={pendingOnly ? 'pending' : 'all'}
            onChange={(v) => setPendingOnly(v === 'pending')}
          >
            <Option value='pending'>{t('Pending Only')}</Option>
            <Option value='all'>{t('All Requests')}</Option>
          </Select>
          <Button icon={<ReloadOutlined />} onClick={loadData} className='me-3'>
            {t('Refresh')}
          </Button>
          <Button type='primary' icon={<PlusOutlined />} onClick={() => setShowSubmit(true)}>
            {t('New Pre-Authorization')}
          </Button>
        </div>
      </div>

      <AntTable
        className='table-layout'
        rowSelection={false}
        dataSource={rows}
        columns={columns}
        loading={loading}
        handleOnChanged={() => {}}
      />

      <SubmitPreAuthModal visible={showSubmit} onClose={() => setShowSubmit(false)} onCreated={loadData} />

      <PreAuthorizationDetailModal
        visible={!!selectedRecord}
        record={selectedRecord}
        onClose={() => setSelectedRecord(null)}
        onChanged={loadData}
      />
    </div>
  )
}

export default PreAuthorizationWorklistController

import React, {FC, useEffect, useState} from 'react'
import {Button, Table, Tag, notification} from 'antd'
import {ReloadOutlined, PlayCircleOutlined, DownloadOutlined} from '@ant-design/icons'
import {BackupApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {DateTimeUtils} from 'src/app/utils'

const STATUS_COLOR: Record<string, string> = {
  running: 'gold',
  success: 'green',
  failed: 'red',
}

const BackupController: FC = () => {
  const {t} = useLang()
  const {handleErrorMessage} = useErrorHandler()
  const [loading, setLoading] = useState(false)
  const [running, setRunning] = useState(false)
  const [backups, setBackups] = useState<any[]>([])

  const loadData = () => {
    setLoading(true)
    BackupApi.list()
      .then((res: any) => setBackups(res?.data?.data ?? res?.data ?? []))
      .catch((err) => handleErrorMessage(err?.response || err))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const handleRunBackup = async () => {
    setRunning(true)
    try {
      await BackupApi.run()
      notification.success({message: t('Backup started successfully')})
      loadData()
    } catch (e: any) {
      notification.error({
        message: t('Failed to run backup'),
        description: e?.response?.data?.message || e?.message,
      })
    } finally {
      setRunning(false)
    }
  }

  const handleDownload = async (row: any) => {
    try {
      const res: any = await BackupApi.download(row.id)
      const blob = new Blob([res.data])
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = row.filename
      a.click()
      window.URL.revokeObjectURL(url)
    } catch (e: any) {
      notification.error({message: t('Failed to download backup')})
    }
  }

  const formatSize = (bytes: number | null) => {
    if (!bytes) return '-'
    const mb = bytes / (1024 * 1024)
    return `${mb.toFixed(2)} MB`
  }

  const columns = [
    {dataIndex: 'filename', key: 'filename', title: t('Filename')},
    {
      dataIndex: 'backup_status',
      key: 'backup_status',
      title: t('Status'),
      render: (v: string) => <Tag color={STATUS_COLOR[v] || 'default'}>{(v || '').toUpperCase()}</Tag>,
    },
    {dataIndex: 'size_bytes', key: 'size_bytes', title: t('Size'), render: formatSize},
    {dataIndex: 'triggered_by_type', key: 'triggered_by_type', title: t('Triggered By')},
    {
      dataIndex: 'started_at',
      key: 'started_at',
      title: t('Started At'),
      render: (v: any) => (v ? DateTimeUtils.formatDateTimeA(v) : '-'),
    },
    {
      dataIndex: 'failure_reason',
      key: 'failure_reason',
      title: t('Failure Reason'),
      render: (v: string) => v || '-',
    },
    {
      dataIndex: 'action',
      key: 'action',
      title: t('Action'),
      render: (_: any, row: any) =>
        row.backup_status === 'success' ? (
          <Button size='small' icon={<DownloadOutlined />} onClick={() => handleDownload(row)}>
            {t('Download')}
          </Button>
        ) : null,
    },
  ]

  return (
    <div className='card p-6'>
      <div className='d-flex justify-content-between align-items-center mb-4'>
        <h3 className='mb-0'>{t('Database Backup')}</h3>
        <div>
          <Button icon={<ReloadOutlined />} onClick={loadData} className='me-3'>
            {t('Refresh')}
          </Button>
          <Button type='primary' icon={<PlayCircleOutlined />} loading={running} onClick={handleRunBackup}>
            {t('Run Backup Now')}
          </Button>
        </div>
      </div>

      <Table rowKey='id' columns={columns} dataSource={backups} loading={loading} pagination={false} />
    </div>
  )
}

export default BackupController

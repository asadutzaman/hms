import React, {FC, useEffect, useState} from 'react'
import {Empty, Skeleton, Table, Tag, Tooltip} from 'antd'
import {DateTimeUtils} from 'src/app/utils'

interface AuditLogEntry {
  id: number
  action: string
  action_label?: string
  old_values?: Record<string, any> | null
  new_values?: Record<string, any> | null
  actor_id?: number | null
  performed_by?: number | null
  occurred_at?: string | null
  performed_at?: string | null
  remarks?: string | null
}

interface AuditLogPanelProps {
  // Returns an axios-style response whose `data` is either an array of
  // entries, or `{ data: [...] }`.
  fetchFn: () => Promise<any>
  // Re-fetch whenever this changes (e.g. entityId, or a reload counter).
  reloadKey?: any
}

const diffFields = (oldValues?: Record<string, any> | null, newValues?: Record<string, any> | null) => {
  if (!oldValues && !newValues) return []
  const keys = Array.from(new Set([...Object.keys(oldValues || {}), ...Object.keys(newValues || {})]))
  return keys
    .filter((k) => JSON.stringify((oldValues || {})[k]) !== JSON.stringify((newValues || {})[k]))
    .map((k) => ({field: k, from: (oldValues || {})[k], to: (newValues || {})[k]}))
}

const AuditLogPanel: FC<AuditLogPanelProps> = ({fetchFn, reloadKey}) => {
  const [loading, setLoading] = useState(false)
  const [entries, setEntries] = useState<AuditLogEntry[]>([])

  useEffect(() => {
    let mounted = true
    setLoading(true)
    fetchFn()
      .then((res: any) => {
        if (!mounted) return
        const data = res?.data?.data ?? res?.data ?? []
        setEntries(Array.isArray(data) ? data : [])
      })
      .catch(() => mounted && setEntries([]))
      .finally(() => mounted && setLoading(false))
    return () => {
      mounted = false
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [reloadKey])

  if (loading) return <Skeleton active paragraph={{rows: 4}} />
  if (!entries.length) return <Empty description='No audit history yet' />

  const columns = [
    {
      title: 'Action',
      dataIndex: 'action_label',
      key: 'action',
      width: '18%',
      render: (label: string, row: AuditLogEntry) => <Tag color='blue'>{label || row.action}</Tag>,
    },
    {
      title: 'Changes',
      key: 'changes',
      render: (_: any, row: AuditLogEntry) => {
        const changes = diffFields(row.old_values, row.new_values)
        if (!changes.length) return row.remarks || '—'
        return (
          <div>
            {changes.map((c) => (
              <div key={c.field} style={{fontSize: 12}}>
                <strong>{c.field}</strong>:{' '}
                <span className='text-muted'>{c.from ?? '—'}</span> {'→'}{' '}
                <span>{c.to ?? '—'}</span>
              </div>
            ))}
            {row.remarks && <div className='text-muted mt-1' style={{fontSize: 12}}>{row.remarks}</div>}
          </div>
        )
      },
    },
    {
      title: 'When',
      key: 'occurred_at',
      width: '18%',
      render: (_: any, row: AuditLogEntry) =>
        row.occurred_at || row.performed_at
          ? DateTimeUtils.formatDateTimeA((row.occurred_at || row.performed_at) as string)
          : '—',
    },
    {
      title: 'By',
      key: 'actor',
      width: '10%',
      render: (_: any, row: AuditLogEntry) => (
        <Tooltip title={`User #${row.actor_id ?? row.performed_by ?? '—'}`}>
          {row.actor_id ?? row.performed_by ?? '—'}
        </Tooltip>
      ),
    },
  ]

  return (
    <Table
      rowKey='id'
      size='small'
      columns={columns}
      dataSource={entries}
      pagination={{pageSize: 10}}
    />
  )
}

export default AuditLogPanel

import React, {FC, useEffect, useState} from 'react'
import {Table, Empty, Skeleton, Tag, Statistic} from 'antd'
import {DrugApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'

const isNearExpiry = (dateStr: string, days: number) => {
  if (!dateStr) return false
  const diffMs = new Date(dateStr).getTime() - Date.now()
  return diffMs <= days * 24 * 60 * 60 * 1000
}

const DrugStockViewTab: FC<any> = (props) => {
  const {itemData} = props
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [totalBalance, setTotalBalance] = useState(0)
  const [batches, setBatches] = useState<any[]>([])

  useEffect(() => {
    if (!itemData?.id) return
    let mounted = true
    setLoading(true)
    DrugApi.stock(itemData.id)
      .then((res: any) => {
        if (!mounted) return
        const data = res?.data?.data ?? res?.data ?? {}
        setTotalBalance(data.total_balance || 0)
        setBatches(data.batches || [])
      })
      .catch(() => {
        if (!mounted) return
        setTotalBalance(0)
        setBatches([])
      })
      .finally(() => mounted && setLoading(false))
    return () => {
      mounted = false
    }
  }, [itemData?.id])

  if (loading) return <Skeleton active paragraph={{rows: 3}} />

  const columns = [
    {title: t('Branch'), dataIndex: 'branch_name', key: 'branch_name', render: (v: string) => v || '-'},
    {title: t('Shelve'), dataIndex: 'shelve_name', key: 'shelve_name', render: (v: string) => v || '-'},
    {title: t('Quantity'), dataIndex: 'balance_quantity', key: 'balance_quantity'},
    {title: t('Unit Price'), dataIndex: 'unit_price', key: 'unit_price'},
    {
      title: t('Expiry Date'),
      dataIndex: 'expire_date',
      key: 'expire_date',
      render: (v: string) =>
        v ? (
          <span>
            {DateTimeUtils.formatDate(v)}{' '}
            {isNearExpiry(v, 30) && <Tag color='red'>{t('Expiring Soon')}</Tag>}
            {!isNearExpiry(v, 30) && isNearExpiry(v, 90) && <Tag color='gold'>{t('Near Expiry')}</Tag>}
          </span>
        ) : (
          '-'
        ),
    },
  ]

  return (
    <div>
      <Statistic title={t('Total Stock on Hand')} value={totalBalance} className='mb-4' />
      {batches.length > 0 ? (
        <Table rowKey='id' size='small' columns={columns} dataSource={batches} pagination={false} />
      ) : (
        <Empty description={t('No stock recorded for this drug yet')} />
      )}
    </div>
  )
}

export default React.memo(DrugStockViewTab)

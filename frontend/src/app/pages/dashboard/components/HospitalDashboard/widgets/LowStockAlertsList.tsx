import {Card, Table, Tag} from 'antd'
import React, {FC} from 'react'
import {useLang} from 'src/app/hooks/useLang'

interface LowStockRow {
  item_id: number
  item_name: string
  item_code: string
  balance_qty: number
  reorder_qty: number
}

const LowStockAlertsList: FC<{data: LowStockRow[]}> = ({data}) => {
  const {t} = useLang()

  const columns = [
    {title: t('Item'), dataIndex: 'item_name', key: 'item_name'},
    {title: t('Code'), dataIndex: 'item_code', key: 'item_code'},
    {
      title: t('Balance / Reorder Qty'),
      key: 'qty',
      render: (_: any, row: LowStockRow) => (
        <Tag color='red'>
          {row.balance_qty} / {row.reorder_qty}
        </Tag>
      ),
    },
  ]

  return (
    <Card className='h-100' title={t('Low Stock & Near-Expiry Alerts')}>
      <Table
        dataSource={data}
        columns={columns}
        rowKey='item_id'
        pagination={{pageSize: 5}}
        size='small'
        locale={{emptyText: t('No data found!')}}
      />
    </Card>
  )
}

export default React.memo(LowStockAlertsList)

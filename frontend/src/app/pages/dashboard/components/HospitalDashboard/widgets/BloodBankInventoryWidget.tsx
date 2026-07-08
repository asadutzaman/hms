import {Card, Table, Tag} from 'antd'
import React, {FC} from 'react'
import {useLang} from 'src/app/hooks/useLang'

interface BloodInventoryRow {
  blood_group: string
  component_type: string
  unit_count: number
}

const LOW_STOCK_THRESHOLD = 3

const BloodBankInventoryWidget: FC<{data: BloodInventoryRow[]}> = ({data}) => {
  const {t} = useLang()

  const columns = [
    {title: t('Blood Group'), dataIndex: 'blood_group', key: 'blood_group'},
    {title: t('Component'), dataIndex: 'component_type', key: 'component_type'},
    {
      title: t('Available Units'),
      dataIndex: 'unit_count',
      key: 'unit_count',
      render: (count: number) => (
        <Tag color={count <= LOW_STOCK_THRESHOLD ? 'red' : 'green'}>{count}</Tag>
      ),
    },
  ]

  return (
    <Card className='h-100' title={t('Blood Bank Inventory')}>
      <Table
        dataSource={data}
        columns={columns}
        rowKey={(row) => `${row.blood_group}-${row.component_type}`}
        pagination={false}
        size='small'
        locale={{emptyText: t('No data found!')}}
      />
    </Card>
  )
}

export default React.memo(BloodBankInventoryWidget)

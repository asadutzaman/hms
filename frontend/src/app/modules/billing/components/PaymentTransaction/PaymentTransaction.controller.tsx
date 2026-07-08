import React, {FC, useEffect, useState} from 'react'
import {Card, Select, Table, Tag, Typography} from 'antd'
import {PaymentTransactionApi} from 'src/app/api'

const {Title} = Typography
const {Option} = Select

const statusColor: Record<string, string> = {
  initiated: 'processing',
  success: 'green',
  failed: 'red',
}

const PaymentTransactionController: FC = () => {
  const [loading, setLoading] = useState(false)
  const [rows, setRows] = useState<any[]>([])
  const [status, setStatus] = useState<string | undefined>(undefined)

  const loadData = (paymentStatus?: string) => {
    setLoading(true)
    PaymentTransactionApi.list(paymentStatus ? {payment_status: paymentStatus} : {})
      .then((res: any) => setRows(res?.data ?? []))
      .catch(() => setRows([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
  }, [])

  return (
    <div className='p-6'>
      <Title level={3}>Online Payment Transactions</Title>
      <Card>
        <div className='mb-4'>
          <Select
            style={{width: 220}}
            placeholder='Filter by status'
            allowClear
            value={status}
            onChange={(v) => {
              setStatus(v)
              loadData(v)
            }}
          >
            <Option value='initiated'>Initiated</Option>
            <Option value='success'>Success</Option>
            <Option value='failed'>Failed</Option>
          </Select>
        </div>
        <Table
          rowKey='id'
          size='small'
          loading={loading}
          dataSource={rows}
          columns={[
            {title: 'Transaction Ref', dataIndex: 'transaction_ref', key: 'transaction_ref'},
            {title: 'Payable Type', dataIndex: 'payable_type', key: 'payable_type'},
            {title: 'Payable ID', dataIndex: 'payable_id', key: 'payable_id'},
            {title: 'Amount', dataIndex: 'amount', key: 'amount'},
            {title: 'Gateway Ref', dataIndex: 'gateway_reference', key: 'gateway_reference', render: (v: any) => v || '-'},
            {
              title: 'Status',
              dataIndex: 'payment_status',
              key: 'payment_status',
              render: (v: string) => <Tag color={statusColor[v] || 'default'}>{v}</Tag>,
            },
            {title: 'Initiated At', dataIndex: 'initiated_at', key: 'initiated_at'},
            {title: 'Completed At', dataIndex: 'completed_at', key: 'completed_at', render: (v: any) => v || '-'},
          ]}
        />
      </Card>
    </div>
  )
}

export default PaymentTransactionController

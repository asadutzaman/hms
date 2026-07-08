import {Card, Table, Tag} from 'antd'
import React, {FC} from 'react'
import {useLang} from 'src/app/hooks/useLang'

interface PendingClaimRow {
  id: number
  claim_no: string
  claimed_amount: number | string
  claim_status: string
  days_in_status: number | null
  patient?: {first_name?: string; last_name?: string}
  insurance_company?: {name?: string}
}

interface InsuranceClaimsData {
  status_counts: {claim_status: string; claim_count: number}[]
  pending_claims: PendingClaimRow[]
}

const STATUS_COLORS: Record<string, string> = {
  draft: 'default',
  submitted: 'blue',
  under_review: 'gold',
  approved: 'green',
  partially_approved: 'gold',
  rejected: 'red',
  settled: 'green',
}

const PendingInsuranceClaimsList: FC<{data: InsuranceClaimsData}> = ({data}) => {
  const {t} = useLang()
  const {pending_claims: pendingClaims} = data

  const columns = [
    {title: t('Claim No.'), dataIndex: 'claim_no', key: 'claim_no'},
    {
      title: t('Patient'),
      key: 'patient',
      render: (_: any, row: PendingClaimRow) =>
        row.patient ? `${row.patient.first_name || ''} ${row.patient.last_name || ''}`.trim() : '-',
    },
    {title: t('Insurer'), key: 'insurer', render: (_: any, row: PendingClaimRow) => row.insurance_company?.name || '-'},
    {title: t('Amount'), dataIndex: 'claimed_amount', key: 'claimed_amount'},
    {
      title: t('Status'),
      dataIndex: 'claim_status',
      key: 'claim_status',
      render: (status: string) => <Tag color={STATUS_COLORS[status] || 'default'}>{t(status)}</Tag>,
    },
    {
      title: t('Days Pending'),
      dataIndex: 'days_in_status',
      key: 'days_in_status',
      render: (days: number | null) => (days !== null ? Math.round(days) : '-'),
    },
  ]

  return (
    <Card className='h-100' title={t('Pending Insurance Claims')}>
      <Table
        dataSource={pendingClaims}
        columns={columns}
        rowKey='id'
        pagination={{pageSize: 5}}
        size='small'
        locale={{emptyText: t('No data found!')}}
      />
    </Card>
  )
}

export default React.memo(PendingInsuranceClaimsList)

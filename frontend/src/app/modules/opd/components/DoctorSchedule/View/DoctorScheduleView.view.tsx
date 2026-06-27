import React, {FC} from 'react'
import {Badge, Descriptions, Card, Table} from 'antd'
import {DateTimeUtils} from 'src/app/utils'

const dayNames: any = {
  1: 'Monday',
  2: 'Tuesday',
  3: 'Wednesday',
  4: 'Thursday',
  5: 'Friday',
  6: 'Saturday',
  7: 'Sunday',
}

const DoctorScheduleViewBody: FC<any> = ({itemData, loading}) => {
  const slots: any[] = itemData?.slots || []

  const slotColumns = [
    {
      title: 'Day',
      dataIndex: 'day_of_week',
      key: 'day_of_week',
      render: (v: number) => dayNames[v] || '-',
    },
    {
      title: 'Start',
      dataIndex: 'start_time',
      key: 'start_time',
    },
    {
      title: 'End',
      dataIndex: 'end_time',
      key: 'end_time',
    },
    {
      title: 'Slot Duration',
      dataIndex: 'slot_duration_minutes',
      key: 'slot_duration_minutes',
      render: (v: number) => (v ? `${v} min` : '-'),
    },
    {
      title: 'Max Patients',
      dataIndex: 'max_patients',
      key: 'max_patients',
    },
    {
      title: 'Status',
      dataIndex: 'is_active',
      key: 'is_active',
      render: (v: boolean) =>
        v ? <Badge status='success' text='Active' /> : <Badge status='default' text='Inactive' />,
    },
  ]

  return (
    <div className='p-3'>
      <Card title='Schedule Information' className='mb-3'>
        <Descriptions bordered column={2} size='small'>
          <Descriptions.Item label='Name' span={2}>
            {itemData?.name || '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Code'>
            {itemData?.code || '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Status'>
            {itemData?.status ? (
              <Badge status='success' text='Active' />
            ) : (
              <Badge status='default' text='Inactive' />
            )}
          </Descriptions.Item>
          <Descriptions.Item label='Doctor'>
            {itemData?.doctor?.name_en ||
              itemData?.doctor?.name ||
              itemData?.doctor_name ||
              '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Department'>
            {itemData?.department?.name || '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Schedule Type'>
            {itemData?.schedule_type || '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Consultation Mode'>
            {itemData?.consultation_mode || '-'}
          </Descriptions.Item>
          <Descriptions.Item label='Effective From'>
            {DateTimeUtils.formatDate(itemData?.effective_from)}
          </Descriptions.Item>
          <Descriptions.Item label='Effective To'>
            {itemData?.effective_to
              ? DateTimeUtils.formatDate(itemData.effective_to)
              : 'Indefinite'}
          </Descriptions.Item>
          <Descriptions.Item label='Default'>
            {itemData?.is_default ? (
              <Badge status='success' text='Yes' />
            ) : (
              <Badge status='default' text='No' />
            )}
          </Descriptions.Item>
          <Descriptions.Item label='Notes' span={2}>
            {itemData?.notes || '-'}
          </Descriptions.Item>
        </Descriptions>
      </Card>

      <Card title='Weekly Time Slots'>
        <Table
          rowKey={(record: any) =>
            `${record.day_of_week}-${record.start_time}`}
          size='small'
          columns={slotColumns}
          dataSource={slots}
          pagination={false}
          loading={loading}
          locale={{emptyText: 'No slots defined'}}
        />
      </Card>

      {(itemData?.created_at || itemData?.updated_at) && (
        <Card title='Meta' className='mt-3'>
          <Descriptions bordered column={2} size='small'>
            <Descriptions.Item label='Created'>
              {DateTimeUtils.formatDateTime(itemData?.created_at)}
            </Descriptions.Item>
            <Descriptions.Item label='Updated'>
              {DateTimeUtils.formatDateTime(itemData?.updated_at)}
            </Descriptions.Item>
            <Descriptions.Item label='Created By'>
              {itemData?.created_by || '-'}
            </Descriptions.Item>
            <Descriptions.Item label='Updated By'>
              {itemData?.updated_by || '-'}
            </Descriptions.Item>
          </Descriptions>
        </Card>
      )}
    </div>
  )
}

export default DoctorScheduleViewBody

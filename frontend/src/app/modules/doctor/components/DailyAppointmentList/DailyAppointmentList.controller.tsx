import React, {FC, useEffect, useState} from 'react'
import {Button, Card, DatePicker, Modal, Space, Table, Tag} from 'antd'
import {HistoryOutlined} from '@ant-design/icons'
import dayjs from 'dayjs'
import {DoctorPortalApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {useLang} from 'src/app/hooks/useLang'
import PatientHistoryPanel from '../PatientHistory/PatientHistoryPanel'

const statusColor: Record<string, string> = {
  confirmed: 'blue',
  checked_in: 'cyan',
  in_consultation: 'gold',
  completed: 'green',
  cancelled: 'red',
}

const DailyAppointmentListController: FC = () => {
  const [loading, setLoading] = useState(false)
  const [date, setDate] = useState(dayjs())
  const [listData, setListData] = useState<any[]>([])
  const [historyPatientId, setHistoryPatientId] = useState<any>(null)
  const {handleErrorMessage} = useErrorHandler()
  const {t} = useLang()

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [date])

  const loadData = () => {
    setLoading(true)
    DoctorPortalApi.appointments({date: date.format('YYYY-MM-DD')})
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? []
        setListData(Array.isArray(data) ? data : [])
        setLoading(false)
      })
      .catch((err) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }

  const columns = [
    {title: t('Token'), dataIndex: 'token_number'},
    {title: t('Appointment No'), dataIndex: 'appointment_no'},
    {title: t('Patient'), dataIndex: 'patient_name'},
    {title: t('Time'), dataIndex: 'appointment_time'},
    {
      title: t('Status'),
      dataIndex: 'status',
      render: (status: string) => <Tag color={statusColor[status] || 'default'}>{status}</Tag>,
    },
    {
      title: t('History'),
      dataIndex: 'action',
      render: (_: any, record: any) => (
        <Button
          size='small'
          icon={<HistoryOutlined />}
          onClick={() => setHistoryPatientId(record.patient_id)}
        >
          {t('View History')}
        </Button>
      ),
    },
  ]

  return (
    <div className='card'>
      <div className='p-6'>
        <Space>
          <DatePicker value={date} onChange={(d) => d && setDate(d)} allowClear={false} />
        </Space>
      </div>
      <div className='p-6'>
        <Card loading={loading}>
          <Table rowKey='id' columns={columns} dataSource={listData} pagination={false} />
        </Card>
      </div>
      <Modal
        title={t('Patient History')}
        open={!!historyPatientId}
        onCancel={() => setHistoryPatientId(null)}
        footer={null}
        width={700}
      >
        <PatientHistoryPanel patientId={historyPatientId} />
      </Modal>
    </div>
  )
}

export default DailyAppointmentListController

import React, {FC, useEffect, useState} from 'react'
import {Card, Col, Row, Statistic, Table, Spin} from 'antd'
import {TeamOutlined, ClockCircleOutlined} from '@ant-design/icons'
import {DoctorPortalApi} from 'src/app/api'
import {useErrorHandler} from 'src/app/hooks/useErrorHandler'
import {useLang} from 'src/app/hooks/useLang'

const DoctorDashboardController: FC = () => {
  const [loading, setLoading] = useState(false)
  const [data, setData] = useState<any>({today_appointment_count: 0, queue_count: 0, queue: []})
  const {handleErrorMessage} = useErrorHandler()
  const {t} = useLang()

  useEffect(() => {
    setLoading(true)
    DoctorPortalApi.dashboard()
      .then((res: any) => {
        setData(res?.data?.data ?? res?.data ?? {})
        setLoading(false)
      })
      .catch((err) => {
        handleErrorMessage(err)
        setLoading(false)
      })
  }, [])

  const columns = [
    {title: t('Token'), dataIndex: 'token_number'},
    {title: t('Patient'), dataIndex: ['patient', 'first_name']},
    {title: t('Status'), dataIndex: 'status'},
  ]

  return (
    <div className='p-6'>
      <Spin spinning={loading}>
        <Row gutter={16} className='mb-6'>
          <Col span={8}>
            <Card>
              <Statistic
                title={t("Today's Appointments")}
                value={data.today_appointment_count}
                prefix={<TeamOutlined />}
              />
            </Card>
          </Col>
          <Col span={8}>
            <Card>
              <Statistic
                title={t('Current Queue')}
                value={data.queue_count}
                prefix={<ClockCircleOutlined />}
              />
            </Card>
          </Col>
        </Row>
        <Card title={t('Waiting Queue')}>
          <Table rowKey='id' columns={columns} dataSource={data.queue || []} pagination={false} />
        </Card>
      </Spin>
    </div>
  )
}

export default DoctorDashboardController

import React, {FC, useEffect, useState} from 'react'
import {Button, Card, Col, Form, Input, Row, Spin, Typography} from 'antd'
import {PatientPortalApi} from 'src/app/api'
import {Message} from 'src/app/utils'

const {Title} = Typography

const PatientProfileController: FC = () => {
  const [loading, setLoading] = useState<boolean>(true)
  const [saving, setSaving] = useState<boolean>(false)
  const [patient, setPatient] = useState<any>(null)
  const [form] = Form.useForm()

  useEffect(() => {
    PatientPortalApi.me()
      .then((res: any) => {
        setPatient(res?.data || null)
        form.setFieldsValue(res?.data || {})
        setLoading(false)
      })
      .catch(() => setLoading(false))
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const handleSave = (values: any) => {
    setSaving(true)
    PatientPortalApi.updateProfile(values)
      .then((res: any) => {
        setPatient(res?.data || null)
        Message.success('Profile updated successfully.')
        setSaving(false)
      })
      .catch(() => {
        Message.error('Could not update your profile. Please try again.')
        setSaving(false)
      })
  }

  return (
    <Spin spinning={loading}>
      <Title level={3}>My Profile</Title>
      <Card className='mb-4'>
        <Row gutter={[16, 16]}>
          <Col span={6}>
            <strong>MRN:</strong> {patient?.mrn || '-'}
          </Col>
          <Col span={6}>
            <strong>Name:</strong> {patient?.first_name} {patient?.last_name}
          </Col>
          <Col span={6}>
            <strong>Email:</strong> {patient?.email || '-'}
          </Col>
          <Col span={6}>
            <strong>Date of Birth:</strong> {patient?.date_of_birth || '-'}
          </Col>
        </Row>
      </Card>

      <Card>
        <Form form={form} layout='vertical' onFinish={handleSave}>
          <Row gutter={[16, 0]}>
            <Col span={12}>
              <Form.Item name='primary_phone' label='Primary Phone'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name='secondary_phone' label='Secondary Phone'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={24}>
              <Form.Item name='current_address' label='Current Address'>
                <Input.TextArea rows={2} />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name='current_city' label='City'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name='current_state' label='State'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name='current_country' label='Country'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name='current_pincode' label='Pincode'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name='emergency_contact_name' label='Emergency Contact Name'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={8}>
              <Form.Item name='emergency_contact_phone' label='Emergency Contact Phone'>
                <Input />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item name='emergency_contact_relation' label='Emergency Contact Relation'>
                <Input />
              </Form.Item>
            </Col>
          </Row>
          <Button type='primary' htmlType='submit' loading={saving}>
            Save Changes
          </Button>
        </Form>
      </Card>
    </Spin>
  )
}

export default PatientProfileController

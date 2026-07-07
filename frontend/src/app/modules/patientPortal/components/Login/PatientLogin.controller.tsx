import React, {FC, useState} from 'react'
import {Button, Card, Form, Input, Typography} from 'antd'
import {useNavigate} from 'react-router-dom'
import {PatientPortalApi} from 'src/app/api'
import PatientStorageService from 'src/app/services/patientStorage.service'
import {PatientHttpService} from 'src/app/services/patientHttp.services'
import {Message} from 'src/app/utils'

const {Title, Paragraph} = Typography
const StorageService = new PatientStorageService()

const PatientLoginController: FC = () => {
  const navigate = useNavigate()
  const [step, setStep] = useState<'email' | 'otp'>('email')
  const [email, setEmail] = useState<string>('')
  const [loading, setLoading] = useState<boolean>(false)

  const handleRequestOtp = (values: {email: string}) => {
    setLoading(true)
    PatientPortalApi.requestOtp({email: values.email})
      .then((res: any) => {
        setEmail(values.email)
        setStep('otp')
        Message.success(res?.data?.message || 'If this email is registered, a login code has been sent.')
        setLoading(false)
      })
      .catch(() => {
        Message.error('A network error occurred. Please try again later.')
        setLoading(false)
      })
  }

  const handleVerifyOtp = (values: {code: string}) => {
    setLoading(true)
    PatientPortalApi.verifyOtp({email, code: values.code})
      .then((res: any) => {
        const data = res?.data || {}
        StorageService.setAccessToken(data.access_token)
        PatientHttpService.setAccessToken(data.access_token)
        setLoading(false)
        navigate('/patient-portal/dashboard')
      })
      .catch((err: any) => {
        const msg = typeof err?.data === 'string' ? err.data : 'Incorrect or expired code. Please try again.'
        Message.error(msg)
        setLoading(false)
      })
  }

  return (
    <div
      style={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: '#f5f5f5',
      }}
    >
      <Card style={{width: 400}}>
        <Title level={3}>Patient Portal Login</Title>
        {step === 'email' && (
          <>
            <Paragraph type='secondary'>
              Enter the email address registered with the hospital. We'll send you a one-time login code.
            </Paragraph>
            <Form layout='vertical' onFinish={handleRequestOtp}>
              <Form.Item
                name='email'
                label='Email'
                rules={[{required: true, message: 'Email is required'}, {type: 'email', message: 'Enter a valid email'}]}
              >
                <Input placeholder='you@example.com' autoFocus />
              </Form.Item>
              <Button type='primary' htmlType='submit' block loading={loading}>
                Send Login Code
              </Button>
            </Form>
          </>
        )}
        {step === 'otp' && (
          <>
            <Paragraph type='secondary'>
              Enter the 6-digit code sent to <strong>{email}</strong>.
            </Paragraph>
            <Form layout='vertical' onFinish={handleVerifyOtp}>
              <Form.Item
                name='code'
                label='Login Code'
                rules={[
                  {required: true, message: 'Code is required'},
                  {len: 6, message: 'Code must be 6 digits'},
                ]}
              >
                <Input placeholder='123456' maxLength={6} autoFocus />
              </Form.Item>
              <Button type='primary' htmlType='submit' block loading={loading}>
                Verify & Login
              </Button>
              <Button type='link' block onClick={() => setStep('email')}>
                Use a different email
              </Button>
            </Form>
          </>
        )}
      </Card>
    </div>
  )
}

export default PatientLoginController

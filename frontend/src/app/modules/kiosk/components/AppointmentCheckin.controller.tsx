import React, {FC, useRef, useState} from 'react'
import {Button, Card, Input, Result, Spin, Typography} from 'antd'
import axios from 'axios'
import {API_SERVER_URL, SERVER_PREFIX} from 'src/app/constants/config.constant'

const {Title, Text} = Typography

/**
 * F-02-08 QR Code Check-in — a genuinely public kiosk page (no staff or
 * patient login), mounted outside both /admin/* and the patient portal.
 * A real reception kiosk's USB QR scanner acts as a keyboard-wedge device
 * — it "types" the scanned code into whatever text field has focus, which
 * is exactly what this input does. No client-side QR *rendering* library
 * was introduced (see project_hms_sprint10_scope memory — this repo's
 * rule against adding new UI libraries) but scanning/entry works
 * identically to a real deployment either way.
 */
const AppointmentCheckinController: FC = () => {
  const [code, setCode] = useState('')
  const [loading, setLoading] = useState(false)
  const [info, setInfo] = useState<any>(null)
  const [result, setResult] = useState<'success' | 'error' | null>(null)
  const [message, setMessage] = useState('')
  const inputRef = useRef<any>(null)

  const baseUrl = `${API_SERVER_URL}${SERVER_PREFIX}/appointment-checkin`

  const handleLookup = async (uuid: string) => {
    if (!uuid.trim()) return
    setLoading(true)
    setResult(null)
    setInfo(null)
    try {
      const res = await axios.get(`${baseUrl}/${uuid.trim()}`)
      setInfo(res.data?.data ?? res.data)
    } catch (err: any) {
      setResult('error')
      setMessage(err?.response?.data?.message || 'Appointment not found.')
    } finally {
      setLoading(false)
    }
  }

  const handleCheckIn = async () => {
    setLoading(true)
    try {
      const res = await axios.post(`${baseUrl}/${code.trim()}`)
      const data = res.data?.data ?? res.data
      setResult('success')
      setMessage(`Checked in. Token number: ${data.token_number ?? '-'}`)
      setInfo(null)
      setCode('')
    } catch (err: any) {
      setResult('error')
      setMessage(err?.response?.data?.message || 'Check-in failed.')
    } finally {
      setLoading(false)
      inputRef.current?.focus()
    }
  }

  const reset = () => {
    setCode('')
    setInfo(null)
    setResult(null)
    setMessage('')
    inputRef.current?.focus()
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
      <Card style={{width: 480}}>
        <Title level={3} className='text-center'>
          Appointment Check-in
        </Title>
        <Text type='secondary' className='d-block text-center mb-4'>
          Scan your appointment QR code, or enter your appointment code below.
        </Text>

        {result ? (
          <Result
            status={result}
            title={result === 'success' ? 'Checked In' : 'Check-in Failed'}
            subTitle={message}
            extra={
              <Button type='primary' onClick={reset}>
                Check In Another Appointment
              </Button>
            }
          />
        ) : (
          <>
            <Input
              ref={inputRef}
              size='large'
              autoFocus
              placeholder='Scan QR or type appointment code'
              value={code}
              onChange={(e) => setCode(e.target.value)}
              onPressEnter={() => handleLookup(code)}
            />
            <Spin spinning={loading}>
              {info && (
                <Card size='small' className='mt-4'>
                  <p>
                    <strong>{info.patient_name}</strong> — {info.appointment_no}
                  </p>
                  <p>
                    Doctor: {info.doctor_name || '-'} <br />
                    Date: {info.appointment_date} at {info.start_time} <br />
                    Token: {info.token_number ?? '-'}
                  </p>
                  {info.can_check_in ? (
                    <Button type='primary' block onClick={handleCheckIn}>
                      Confirm Check-in
                    </Button>
                  ) : (
                    <Text type='danger'>This appointment cannot be checked in (status: {info.status}).</Text>
                  )}
                </Card>
              )}
              {!info && (
                <Button className='mt-4' block onClick={() => handleLookup(code)}>
                  Look Up Appointment
                </Button>
              )}
            </Spin>
          </>
        )}
      </Card>
    </div>
  )
}

export default AppointmentCheckinController

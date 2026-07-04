import React, {FC, useCallback, useEffect, useRef, useState} from 'react'
import {Card, Col, Row, Tag, Empty} from 'antd'
import {SoundOutlined} from '@ant-design/icons'
import {OpdVisitApi} from 'src/app/api'

const POLL_INTERVAL_MS = 8000

interface QueueToken {
  opd_visit_id: number
  token_number: number | null
  status: string
  called_at: string | null
  patient_name: string
}

interface DoctorGroup {
  doctor_id: number
  doctor_name: string | null
  department: string | null
  now_serving: number | null
  tokens: QueueToken[]
}

// Base64 short beep so the board doesn't depend on an external asset.
const BEEP_SOUND =
  'data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQAAAAA='

const DisplayBoardController: FC<any> = () => {
  const [groups, setGroups] = useState<DoctorGroup[]>([])
  const calledTokenIds = useRef<Set<number>>(new Set())
  const audioRef = useRef<HTMLAudioElement | null>(null)

  const fetchBoard = useCallback(async () => {
    try {
      const res: any = await OpdVisitApi.displayBoard()
      const data: DoctorGroup[] = res?.data?.data ?? res?.data ?? []
      const nextGroups = Array.isArray(data) ? data : []

      // Detect newly-called tokens (called_at set, not seen before) and beep.
      let hasNewCall = false
      nextGroups.forEach((g) =>
        g.tokens.forEach((t) => {
          if (t.called_at && !calledTokenIds.current.has(t.opd_visit_id)) {
            hasNewCall = true
            calledTokenIds.current.add(t.opd_visit_id)
          }
        })
      )
      if (hasNewCall) {
        audioRef.current?.play().catch(() => {
          /* autoplay may be blocked until the user interacts with the page */
        })
      }

      setGroups(nextGroups)
    } catch (e) {
      console.error('Failed to load display board', e)
    }
  }, [])

  useEffect(() => {
    fetchBoard()
    const interval = setInterval(fetchBoard, POLL_INTERVAL_MS)
    return () => clearInterval(interval)
  }, [fetchBoard])

  return (
    <div className='card p-4' style={{minHeight: '100vh', background: '#0b1220'}}>
      <audio ref={audioRef} src={BEEP_SOUND} />
      <div className='d-flex align-items-center justify-content-between mb-4'>
        <h1 style={{color: '#fff', margin: 0}}>OPD Queue — Display Board</h1>
        <Tag icon={<SoundOutlined />} color='processing' style={{fontSize: 14}}>
          Live · refreshes every {POLL_INTERVAL_MS / 1000}s
        </Tag>
      </div>

      {groups.length === 0 ? (
        <Empty description='No active tokens right now' style={{marginTop: 80}} />
      ) : (
        <Row gutter={[24, 24]}>
          {groups.map((g) => (
            <Col xs={24} md={12} lg={8} key={g.doctor_id}>
              <Card
                style={{background: '#111a2c', border: '1px solid #223'}}
                headStyle={{color: '#fff', borderBottom: '1px solid #223'}}
                bodyStyle={{color: '#fff'}}
                title={
                  <div>
                    <div style={{fontSize: 18}}>{g.doctor_name || `Doctor #${g.doctor_id}`}</div>
                    <div style={{fontSize: 13, color: '#8aa'}}>{g.department || ''}</div>
                  </div>
                }
              >
                <div className='text-center mb-4'>
                  <div style={{fontSize: 14, color: '#8aa'}}>NOW SERVING</div>
                  <div style={{fontSize: 64, fontWeight: 700, lineHeight: 1}}>
                    {g.now_serving ?? '—'}
                  </div>
                </div>

                <div style={{fontSize: 13, color: '#8aa', marginBottom: 8}}>WAITING</div>
                <div className='d-flex flex-wrap gap-2'>
                  {g.tokens
                    .filter((t) => t.status !== 'in_consultation')
                    .map((t) => (
                      <Tag
                        key={t.opd_visit_id}
                        color={t.called_at ? 'gold' : 'default'}
                        style={{fontSize: 16, padding: '6px 12px'}}
                      >
                        #{t.token_number ?? '-'} {t.called_at ? '📢' : ''}
                      </Tag>
                    ))}
                  {g.tokens.filter((t) => t.status !== 'in_consultation').length === 0 && (
                    <span style={{color: '#8aa'}}>No one waiting</span>
                  )}
                </div>
              </Card>
            </Col>
          ))}
        </Row>
      )}
    </div>
  )
}

export default DisplayBoardController

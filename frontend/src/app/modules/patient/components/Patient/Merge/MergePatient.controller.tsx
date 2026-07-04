import React, {FC, useState} from 'react'
import {AutoComplete, Button, Card, Col, Input, Row, Space, Table, notification} from 'antd'
import {UserOutlined, SwapOutlined} from '@ant-design/icons'
import {useNavigate} from 'react-router-dom'
import {PatientApi} from 'src/app/api'

interface PatientOption {
  id: number
  mrn: number | string
  first_name: string
  last_name: string
  full_name?: string
  primary_phone: string
  date_of_birth?: string
  email?: string
}

const COMPARE_FIELDS: {key: keyof PatientOption | string; label: string}[] = [
  {key: 'mrn', label: 'MRN'},
  {key: 'full_name', label: 'Full Name'},
  {key: 'primary_phone', label: 'Primary Phone'},
  {key: 'email', label: 'Email'},
  {key: 'date_of_birth', label: 'Date of Birth'},
]

const MergePatientController: FC<any> = () => {
  const navigate = useNavigate()

  const [survivorSearch, setSurvivorSearch] = useState('')
  const [duplicateSearch, setDuplicateSearch] = useState('')
  const [survivorOptions, setSurvivorOptions] = useState<PatientOption[]>([])
  const [duplicateOptions, setDuplicateOptions] = useState<PatientOption[]>([])
  const [survivor, setSurvivor] = useState<PatientOption | null>(null)
  const [duplicate, setDuplicate] = useState<PatientOption | null>(null)
  const [submitting, setSubmitting] = useState(false)

  const searchPatients = async (
    text: string,
    setOptions: (opts: PatientOption[]) => void
  ) => {
    if (!text || text.length < 2) {
      setOptions([])
      return
    }
    try {
      const res: any = await PatientApi.getByWhere({$search: text, $top: 10})
      setOptions(res?.data?.results || [])
    } catch {
      setOptions([])
    }
  }

  const handleMerge = async () => {
    if (!survivor || !duplicate) {
      notification.warning({message: 'Select both the record to keep and the duplicate to merge.'})
      return
    }
    if (survivor.id === duplicate.id) {
      notification.warning({message: 'Survivor and duplicate must be different patients.'})
      return
    }

    setSubmitting(true)
    try {
      const res: any = await PatientApi.merge({
        survivor_patient_id: survivor.id,
        duplicate_patient_id: duplicate.id,
      })
      const reassigned = res?.data?.reassigned || {}
      const totalReassigned = Object.values(reassigned).reduce(
        (sum: number, n: any) => sum + (Number(n) || 0),
        0
      )
      notification.success({
        message: 'Patients merged',
        description: `Patient #${duplicate.id} was merged into #${survivor.id}. ${totalReassigned} related record(s) reassigned.`,
      })
      navigate(`/admin/patient/list`)
    } catch (e: any) {
      notification.error({
        message: 'Merge failed',
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSubmitting(false)
    }
  }

  const renderPicker = (
    label: string,
    value: PatientOption | null,
    options: PatientOption[],
    onSearch: (text: string) => void,
    onSelect: (p: PatientOption) => void
  ) => (
    <Card title={label} size='small'>
      <AutoComplete
        style={{width: '100%'}}
        options={options.map((p) => ({
          value: p.id,
          label: (
            <div>
              <strong>{p.full_name || `${p.first_name} ${p.last_name}`}</strong>{' '}
              <span className='text-muted'>(MRN {p.mrn})</span>
              <div className='text-muted fs-7'>{p.primary_phone}</div>
            </div>
          ),
          raw: p,
        }))}
        onSearch={onSearch}
        onSelect={(_v: any, option: any) => onSelect(option.raw)}
        placeholder='Search by name, phone or MRN'
      >
        <Input prefix={<UserOutlined />} placeholder='Type at least 2 characters...' />
      </AutoComplete>
      {value && (
        <div className='alert alert-info mt-3 mb-0'>
          <strong>{value.full_name || `${value.first_name} ${value.last_name}`}</strong>
          <br />
          MRN: {value.mrn} · Phone: {value.primary_phone || '-'}
        </div>
      )}
    </Card>
  )

  const comparisonColumns = [
    {title: 'Field', dataIndex: 'label', key: 'label', width: '20%'},
    {
      title: 'Keep (Survivor)',
      dataIndex: 'survivorValue',
      key: 'survivorValue',
    },
    {
      title: 'Merge Away (Duplicate)',
      dataIndex: 'duplicateValue',
      key: 'duplicateValue',
    },
  ]

  const comparisonData = COMPARE_FIELDS.map((f) => ({
    key: f.key,
    label: f.label,
    survivorValue: (survivor as any)?.[f.key] ?? '-',
    duplicateValue: (duplicate as any)?.[f.key] ?? '-',
  }))

  return (
    <div className='card p-4'>
      <h2 className='mb-1'>Merge Duplicate Patient Records</h2>
      <p className='text-muted'>
        Reassigns appointments and OPD visits from the duplicate record onto the survivor, then
        soft-deletes the duplicate. This action is logged in the patient audit trail.
      </p>

      <Row gutter={16} className='mb-4'>
        <Col md={12} xs={24}>
          {renderPicker('Record to Keep', survivor, survivorOptions, (t) => {
            setSurvivorSearch(t)
            searchPatients(t, setSurvivorOptions)
          }, setSurvivor)}
        </Col>
        <Col md={12} xs={24}>
          {renderPicker('Duplicate to Merge', duplicate, duplicateOptions, (t) => {
            setDuplicateSearch(t)
            searchPatients(t, setDuplicateOptions)
          }, setDuplicate)}
        </Col>
      </Row>

      {survivor && duplicate && (
        <Card title='Field Comparison' className='mb-4' size='small'>
          <Table
            rowKey='key'
            columns={comparisonColumns}
            dataSource={comparisonData}
            pagination={false}
            size='small'
          />
        </Card>
      )}

      <Space>
        <Button
          type='primary'
          icon={<SwapOutlined />}
          size='large'
          loading={submitting}
          disabled={!survivor || !duplicate}
          onClick={handleMerge}
        >
          Merge Records
        </Button>
        <Button size='large' onClick={() => navigate('/admin/patient/list')}>
          Cancel
        </Button>
      </Space>
    </div>
  )
}

export default MergePatientController

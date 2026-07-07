import React, {FC, useEffect, useState} from 'react'
import {Input, InputNumber, Select, Button, notification, Popconfirm} from 'antd'
import {PlusOutlined, DeleteOutlined, SaveOutlined} from '@ant-design/icons'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {LabTestAction} from '../Actions/LabTest.actions'
import {LabTestApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

const {Option} = Select

interface ReferenceRangeRow {
  gender: string
  age_min_years: number
  age_max_years: number | null
  range_low: number | null
  range_high: number | null
  range_text: string | null
}

interface ParameterRow {
  parameter_name: string
  unit: string | null
  result_data_type: string
  critical_low: number | null
  critical_high: number | null
  reference_ranges: ReferenceRangeRow[]
}

const emptyRange = (): ReferenceRangeRow => ({
  gender: 'all',
  age_min_years: 0,
  age_max_years: null,
  range_low: null,
  range_high: null,
  range_text: null,
})

const emptyParameter = (): ParameterRow => ({
  parameter_name: '',
  unit: null,
  result_data_type: 'numeric',
  critical_low: null,
  critical_high: null,
  reference_ranges: [emptyRange()],
})

const LabTestView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  const [parameters, setParameters] = useState<ParameterRow[]>([])
  const [saving, setSaving] = useState(false)

  useEffect(() => {
    const existing = (itemData?.parameters || []).map((p: any) => ({
      parameter_name: p.parameter_name,
      unit: p.unit,
      result_data_type: p.result_data_type || 'numeric',
      critical_low: p.critical_low,
      critical_high: p.critical_high,
      reference_ranges: (p.reference_ranges || []).map((r: any) => ({
        gender: r.gender || 'all',
        age_min_years: r.age_min_years ?? 0,
        age_max_years: r.age_max_years,
        range_low: r.range_low,
        range_high: r.range_high,
        range_text: r.range_text,
      })),
    }))
    setParameters(existing)
  }, [itemData?.id, itemData?.parameters])

  const addParameter = () => setParameters([...parameters, emptyParameter()])
  const removeParameter = (idx: number) => setParameters(parameters.filter((_, i) => i !== idx))

  const updateParameter = (idx: number, field: keyof ParameterRow, value: any) => {
    const next = [...parameters]
    ;(next[idx] as any)[field] = value
    setParameters(next)
  }

  const addRange = (paramIdx: number) => {
    const next = [...parameters]
    next[paramIdx].reference_ranges = [...next[paramIdx].reference_ranges, emptyRange()]
    setParameters(next)
  }

  const removeRange = (paramIdx: number, rangeIdx: number) => {
    const next = [...parameters]
    next[paramIdx].reference_ranges = next[paramIdx].reference_ranges.filter((_, i) => i !== rangeIdx)
    setParameters(next)
  }

  const updateRange = (paramIdx: number, rangeIdx: number, field: keyof ReferenceRangeRow, value: any) => {
    const next = [...parameters]
    ;(next[paramIdx].reference_ranges[rangeIdx] as any)[field] = value
    setParameters(next)
  }

  const handleSaveParameters = async () => {
    if (!itemData?.id) return
    setSaving(true)
    try {
      await LabTestApi.updateParameters(itemData.id, {parameters})
      notification.success({message: t('Parameters saved successfully')})
      handleCallbackFunc(null, 'reloadView')
    } catch (e: any) {
      notification.error({
        message: t('Failed to save parameters'),
        description: e?.response?.data?.message || e?.message || 'Unknown error',
      })
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction
            entityId={itemData.id}
            actionItem={LabTestAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={LabTestAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>

      <div className='table-responsive mb-8'>
        <table className='table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4'>
          <tbody>
            <tr>
              <td width={'20%'}>{t('Code')}</td>
              <td width={'5%'}>:</td>
              <td width={'75%'}>{itemData.code}</td>
            </tr>
            <tr>
              <td>{t('Name')}</td>
              <td>:</td>
              <td>{itemData.name}</td>
            </tr>
            <tr>
              <td>{t('Category')}</td>
              <td>:</td>
              <td>{itemData.category}</td>
            </tr>
            <tr>
              <td>{t('Sample Type')}</td>
              <td>:</td>
              <td>{itemData.sample_type}</td>
            </tr>
            <tr>
              <td>{t('TAT (hours)')}</td>
              <td>:</td>
              <td>{itemData.tat_hours}</td>
            </tr>
            <tr>
              <td>{t('Default Price')}</td>
              <td>:</td>
              <td>{itemData.default_price}</td>
            </tr>
            <tr>
              <td>{t('Description')}</td>
              <td>:</td>
              <td>{itemData.description}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div className='d-flex justify-content-between align-items-center mb-4'>
        <h4 className='mb-0'>{t('Result Parameters & Reference Ranges')}</h4>
        <div>
          <Button icon={<PlusOutlined />} onClick={addParameter} className='me-2'>
            {t('Add Parameter')}
          </Button>
          <Button type='primary' icon={<SaveOutlined />} loading={saving} onClick={handleSaveParameters}>
            {t('Save Parameters')}
          </Button>
        </div>
      </div>

      {parameters.length === 0 && (
        <div className='text-muted mb-4'>{t('No parameters defined yet for this test.')}</div>
      )}

      {parameters.map((param, pIdx) => (
        <div key={pIdx} className='border rounded p-4 mb-4'>
          <div className='row g-3 align-items-end'>
            <div className='col-md-3'>
              <label className='form-label'>{t('Parameter Name')}</label>
              <Input
                value={param.parameter_name}
                onChange={(e) => updateParameter(pIdx, 'parameter_name', e.target.value)}
              />
            </div>
            <div className='col-md-2'>
              <label className='form-label'>{t('Unit')}</label>
              <Input value={param.unit || ''} onChange={(e) => updateParameter(pIdx, 'unit', e.target.value)} />
            </div>
            <div className='col-md-2'>
              <label className='form-label'>{t('Data Type')}</label>
              <Select
                style={{width: '100%'}}
                value={param.result_data_type}
                onChange={(v) => updateParameter(pIdx, 'result_data_type', v)}
              >
                <Option value='numeric'>{t('Numeric')}</Option>
                <Option value='text'>{t('Text')}</Option>
                <Option value='select'>{t('Select')}</Option>
              </Select>
            </div>
            <div className='col-md-2'>
              <label className='form-label'>{t('Critical Low')}</label>
              <InputNumber
                style={{width: '100%'}}
                value={param.critical_low as any}
                onChange={(v) => updateParameter(pIdx, 'critical_low', v)}
              />
            </div>
            <div className='col-md-2'>
              <label className='form-label'>{t('Critical High')}</label>
              <InputNumber
                style={{width: '100%'}}
                value={param.critical_high as any}
                onChange={(v) => updateParameter(pIdx, 'critical_high', v)}
              />
            </div>
            <div className='col-md-1 text-end'>
              <Popconfirm title={t('Remove this parameter?')} onConfirm={() => removeParameter(pIdx)}>
                <Button danger icon={<DeleteOutlined />} />
              </Popconfirm>
            </div>
          </div>

          <div className='mt-4'>
            <div className='d-flex justify-content-between align-items-center mb-2'>
              <label className='form-label mb-0'>{t('Reference Ranges')}</label>
              <Button size='small' icon={<PlusOutlined />} onClick={() => addRange(pIdx)}>
                {t('Add Range')}
              </Button>
            </div>
            {param.reference_ranges.map((range, rIdx) => (
              <div key={rIdx} className='row g-2 align-items-center mb-2'>
                <div className='col-md-2'>
                  <Select
                    style={{width: '100%'}}
                    value={range.gender}
                    onChange={(v) => updateRange(pIdx, rIdx, 'gender', v)}
                  >
                    <Option value='all'>{t('All')}</Option>
                    <Option value='male'>{t('Male')}</Option>
                    <Option value='female'>{t('Female')}</Option>
                  </Select>
                </div>
                <div className='col-md-2'>
                  <InputNumber
                    placeholder={t('Age Min')}
                    style={{width: '100%'}}
                    value={range.age_min_years as any}
                    onChange={(v) => updateRange(pIdx, rIdx, 'age_min_years', v)}
                  />
                </div>
                <div className='col-md-2'>
                  <InputNumber
                    placeholder={t('Age Max')}
                    style={{width: '100%'}}
                    value={range.age_max_years as any}
                    onChange={(v) => updateRange(pIdx, rIdx, 'age_max_years', v)}
                  />
                </div>
                <div className='col-md-2'>
                  <InputNumber
                    placeholder={t('Low')}
                    style={{width: '100%'}}
                    value={range.range_low as any}
                    onChange={(v) => updateRange(pIdx, rIdx, 'range_low', v)}
                  />
                </div>
                <div className='col-md-2'>
                  <InputNumber
                    placeholder={t('High')}
                    style={{width: '100%'}}
                    value={range.range_high as any}
                    onChange={(v) => updateRange(pIdx, rIdx, 'range_high', v)}
                  />
                </div>
                <div className='col-md-1'>
                  <Button danger size='small' icon={<DeleteOutlined />} onClick={() => removeRange(pIdx, rIdx)} />
                </div>
              </div>
            ))}
          </div>
        </div>
      ))}
    </div>
  )
}
export default React.memo(LabTestView)

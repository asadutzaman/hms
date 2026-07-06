import React, {FC} from 'react'
import {Input, Form, Select} from 'antd'
import {IpdAdmissionAction} from '../Actions/IpdAdmission.actions'
import {Col, Row} from 'react-bootstrap'
import CreateAction from 'src/app/components/Actions/CreateAction'
import {RefreshIcon, ResetIcon} from 'src/app/../_metronic/assets/images/icon/svg'
import {useLang} from 'src/app/hooks/useLang'

const {Option} = Select

const IpdAdmissionListFilter: FC<any> = (props) => {
  const {Search} = Input
  const {filters, handleOnChanged, handleCallbackFunc} = props
  const {t} = useLang()

  return (
    <div className='p-6'>
      <Row gutter={[16, 16]}>
        <Col md={6} xs={12}>
          <div className='card card-header p-0 pb-3' style={{minHeight: '0px'}}>
            <h3 className='card-title align-items-start flex-column'>
              <span className='card-label fw-bold fs-3 mb-1'>{t('IPD Admissions')}</span>
            </h3>
          </div>
        </Col>
        <Col md={6} xs={12}>
          <CreateAction
            actionItem={IpdAdmissionAction.COMMON_ACTION.CREATE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </Col>
      </Row>

      <Row gutter={[16, 16]}>
        <Col md={4} xs={12}>
          <Form.Item name='search'>
            <Search
              placeholder={t('Search by admission no / diagnosis')}
              onSearch={(value) => handleOnChanged('search', value)}
            />
          </Form.Item>
        </Col>

        <Col md={4} xs={12}>
          <Select
            allowClear
            placeholder={t('Admission Status')}
            style={{width: '100%'}}
            value={filters?.admission_status || undefined}
            onChange={(value) => handleOnChanged('filter_admission_status', value)}
          >
            <Option value='admitted'>{t('Admitted')}</Option>
            <Option value='discharged'>{t('Discharged')}</Option>
            <Option value='dama'>{t('DAMA')}</Option>
            <Option value='deceased'>{t('Deceased')}</Option>
          </Select>
        </Col>

        <Col md={4} xs={12}>
          <div className='d-flex justify-content-end'>
            <button
              title={t('Reset')}
              type='button'
              className='btn btn-sm btn-light-primary me-3'
              onClick={(event) => handleCallbackFunc(null, 'resetListing')}
            >
              <ResetIcon />
            </button>

            <button
              title={t('Refresh')}
              type='button'
              className='btn btn-sm btn-light-primary me-3'
              onClick={(event) => handleCallbackFunc(null, 'reloadListing')}
            >
              <RefreshIcon />
            </button>
          </div>
        </Col>
      </Row>
    </div>
  )
}
export default React.memo(IpdAdmissionListFilter)

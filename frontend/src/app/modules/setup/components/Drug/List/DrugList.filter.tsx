import React, {FC} from 'react'
import {Input, Form} from 'antd'
import {DrugAction} from '../Actions/Drug.actions'
import {Col, Row} from 'react-bootstrap'
import CreateAction from 'src/app/components/Actions/CreateAction'
import {RefreshIcon, ResetIcon} from 'src/app/../_metronic/assets/images/icon/svg'
import {useLang} from 'src/app/hooks/useLang'

const DrugListFilter: FC<any> = (props) => {
  const {Search} = Input
  const {handleOnChanged, handleCallbackFunc} = props
  const {t} = useLang()

  return (
    <div className='p-6'>
      <Row>
        <Col md={6} xs={12}>
          <div className='card card-header p-0 pb-3' style={{minHeight: '0px'}}>
            <h3 className='card-title align-items-start flex-column'>
              <span className='card-label fw-bold fs-3 mb-1'>{t('Drug List')}</span>
              <span className='text-muted mt-1 fw-semibold fs-7'>
                {t('Pharmacy master data — drugs, generics and brand mapping')}
              </span>
            </h3>
          </div>
        </Col>
        <Col md={6} xs={12}>
          <CreateAction actionItem={DrugAction.COMMON_ACTION.CREATE} handleCallbackFunc={handleCallbackFunc} />
        </Col>
      </Row>

      <Row>
        <Col md={4} xs={12}>
          <Form.Item name='search'>
            <Search
              placeholder={t('Search by generic/brand name')}
              onSearch={(value) => handleOnChanged('search', value)}
            />
          </Form.Item>
        </Col>
        <Col md={8} xs={12}>
          <div className='d-flex justify-content-end'>
            <button
              title={t('Reset')}
              type='button'
              className='btn btn-sm btn-light-primary me-3'
              onClick={() => handleCallbackFunc(null, 'resetListing')}
            >
              <ResetIcon />
            </button>
            <button
              title={t('Refresh')}
              type='button'
              className='btn btn-sm btn-light-primary me-3'
              onClick={() => handleCallbackFunc(null, 'reloadListing')}
            >
              <RefreshIcon />
            </button>
          </div>
        </Col>
      </Row>
    </div>
  )
}

export default React.memo(DrugListFilter)

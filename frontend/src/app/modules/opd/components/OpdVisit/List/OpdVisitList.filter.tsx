import React, {FC, useEffect, useState} from 'react'
import {Input, Select, Form, DatePicker} from 'antd'
import {Col, Row} from 'react-bootstrap'
import {OpdVisitAction} from '../Actions/OpdVisit.actions'
import CreateAction from 'src/app/components/Actions/CreateAction'
import BulkAction from 'src/app/components/Actions/BulkAction'
import {RefreshIcon, ResetIcon} from 'src/app/../_metronic/assets/images/icon/svg'
import {DepartmentApi} from 'src/app/api'

const {RangePicker} = DatePicker

const OpdVisitListFilter: FC<any> = (props) => {
  const {Search} = Input
  const {Option} = Select
  const {filters, handleOnChanged, handleCallbackFunc} = props

  const [departments, setDepartments] = useState<any[]>([])

  useEffect(() => {
    DepartmentApi.dropdown({status: 1})
      .then((res: any) => {
        setDepartments(res?.data?.results || [])
      })
      .catch(() => setDepartments([]))
  }, [])

  return (
    <div className='p-6'>
      <Row gutter={[16, 16]}>
        <Col md={6} xs={12}>
          <div className='card card-header p-0 pb-3' style={{minHeight: '0px'}}>
            <h3 className='card-title align-items-start flex-column'>
              <span className='card-label fw-bold fs-3 mb-1'>OpdVisits</span>
              <span className='text-muted mt-1 fw-semibold fs-7'>
                Manage bookings, walk-ins and queue
              </span>
            </h3>
          </div>
        </Col>
        <Col md={6} xs={12}>
          <CreateAction
            actionItem={OpdVisitAction.COMMON_ACTION.CREATE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </Col>
      </Row>

      <Row gutter={[16, 16]}>
        <Col md={4} xs={12}>
          <Form.Item name='search'>
            <Search
              placeholder='Search by OpdVisit #, patient name or phone'
              onSearch={(value) => handleOnChanged('search', value)}
            />
          </Form.Item>
        </Col>

        <Col md={3} xs={12}>
          <Form.Item name='OpdVisit_date_range' label='Date'>
            <RangePicker
              onChange={(_dates, dateStrings: any) => {
                handleOnChanged('filter_date_from', dateStrings[0] || '')
                handleOnChanged('filter_date_to', dateStrings[1] || '')
              }}
              format='YYYY-MM-DD'
              allowClear
            />
          </Form.Item>
        </Col>

        <Col md={2} xs={12}>
          <Form.Item name='status' label='Status'>
            <Select
              showSearch
              popupMatchSelectWidth={140}
              defaultValue={filters.status}
              optionFilterProp='children'
              onChange={(value) => handleOnChanged('filter_status', value)}
              filterOption={(input, option: any) =>
                option?.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
              }
            >
              <Option value=''>All</Option>
              <Option value='waiting'>Waiting</Option>
              <Option value='vitals_taken'>Vitals Taken</Option>
              <Option value='in_consultation'>In Consultation</Option>
              <Option value='completed'>Completed</Option>
              <Option value='billed'>Billed</Option>
              <Option value='closed'>Closed</Option>
              <Option value='cancelled'>Cancelled</Option>
            </Select>
          </Form.Item>
        </Col>

        <Col md={2} xs={12}>
          <Form.Item name='source' label='Source'>
            <Select
              showSearch
              popupMatchSelectWidth={130}
              defaultValue={filters.source}
              optionFilterProp='children'
              onChange={(value) => handleOnChanged('filter_source', value)}
              filterOption={(input, option: any) =>
                option?.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
              }
            >
              <Option value=''>All</Option>
              <Option value='online'>Online</Option>
              <Option value='walk_in'>Walk-in</Option>
              <Option value='phone'>Phone</Option>
              <Option value='referral'>Referral</Option>
              <Option value='follow_up'>Follow-up</Option>
            </Select>
          </Form.Item>
        </Col>

        <Col md={2} xs={12}>
          <Form.Item name='consultation_mode' label='Mode'>
            <Select
              showSearch
              popupMatchSelectWidth={130}
              defaultValue={filters.consultation_mode}
              optionFilterProp='children'
              onChange={(value) =>
                handleOnChanged('filter_consultation_mode', value)
              }
              filterOption={(input, option: any) =>
                option?.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
              }
            >
              <Option value=''>All</Option>
              <Option value='in_person'>In Person</Option>
              <Option value='telemedicine'>Telemedicine</Option>
              <Option value='home_visit'>Home Visit</Option>
            </Select>
          </Form.Item>
        </Col>

        <Col md={3} xs={12}>
          <Form.Item name='department_id' label='Department'>
            <Select
              showSearch
              popupMatchSelectWidth={180}
              defaultValue={filters.department_id}
              optionFilterProp='children'
              onChange={(value) => handleOnChanged('filter_department_id', value)}
              filterOption={(input, option: any) =>
                option?.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
              }
            >
              <Option value=''>All</Option>
              {departments.map((dept: any) => (
                <Option key={dept.id} value={dept.id}>
                  {dept.name}
                </Option>
              ))}
            </Select>
          </Form.Item>
        </Col>

        <Col md={2} xs={12}>
          <div className='d-flex justify-content-end'>
            <button
              title='Reset'
              type='button'
              className='btn btn-sm btn-light-primary me-3'
              onClick={() => handleCallbackFunc(null, 'resetListing')}
            >
              <ResetIcon />
            </button>

            <button
              title='Refresh'
              type='button'
              className='btn btn-sm btn-light-primary me-3'
              onClick={() => handleCallbackFunc(null, 'reloadListing')}
            >
              <RefreshIcon />
            </button>

            <BulkAction
              bulkAction={OpdVisitAction.BULK_ACTION}
              handleCallbackFunc={handleCallbackFunc}
            />
          </div>
        </Col>
      </Row>
    </div>
  )
}

export default React.memo(OpdVisitListFilter)

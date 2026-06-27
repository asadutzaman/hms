import React, {FC} from 'react'
import {Input, Select, Form} from 'antd'
import {Col, Row} from 'react-bootstrap'
import {DoctorScheduleAction} from '../Actions/DoctorSchedule.actions'
import CreateAction from 'src/app/components/Actions/CreateAction'
import BulkAction from 'src/app/components/Actions/BulkAction'
import {RefreshIcon, ResetIcon} from 'src/app/../_metronic/assets/images/icon/svg'

const DoctorScheduleListFilter: FC<any> = (props) => {
  const {Search} = Input
  const {Option} = Select
  const {filters, handleOnChanged, handleCallbackFunc} = props

  return (
    <div className='p-6'>
      <Row gutter={[16, 16]}>
        <Col md={6} xs={12}>
          <div className='card card-header p-0 pb-3' style={{minHeight: '0px'}}>
            <h3 className='card-title align-items-start flex-column'>
              <span className='card-label fw-bold fs-3 mb-1'>
                Doctor Schedules
              </span>
              <span className='text-muted mt-1 fw-semibold fs-7'>
                Doctor availability templates
              </span>
            </h3>
          </div>
        </Col>
        <Col md={6} xs={12}>
          <CreateAction
            actionItem={DoctorScheduleAction.COMMON_ACTION.CREATE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </Col>
      </Row>

      <Row gutter={[16, 16]}>
        <Col md={4} xs={12}>
          <Form.Item name='search'>
            <Search
              placeholder='Search by name or doctor'
              onSearch={(value) => handleOnChanged('search', value)}
            />
          </Form.Item>
        </Col>

        <Col md={3} xs={12}>
          <Form.Item name='schedule_type' label='Type'>
            <Select
              showSearch
              popupMatchSelectWidth={140}
              defaultValue={filters.schedule_type}
              optionFilterProp='children'
              onChange={(value) =>
                handleOnChanged('filter_schedule_type', value)
              }
              filterOption={(input, option: any) =>
                option?.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
              }
            >
              <Option value=''>All</Option>
              <Option value='regular'>Regular</Option>
              <Option value='temporary'>Temporary</Option>
              <Option value='emergency'>Emergency</Option>
              <Option value='on_call'>On Call</Option>
            </Select>
          </Form.Item>
        </Col>

        <Col md={3} xs={12}>
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

        <Col md={2} xs={12}>
          <Form.Item name='status' label='Status'>
            <Select
              showSearch
              popupMatchSelectWidth={100}
              defaultValue={filters.status}
              optionFilterProp='children'
              onChange={(value) => handleOnChanged('filter_status', value)}
              filterOption={(input, option: any) =>
                option?.children.toLowerCase().indexOf(input.toLowerCase()) >= 0
              }
            >
              <Option value=''>All</Option>
              <Option value='1'>Active</Option>
              <Option value='0'>Inactive</Option>
            </Select>
          </Form.Item>
        </Col>

        <Col md={4} xs={12}>
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
              bulkAction={DoctorScheduleAction.BULK_ACTION}
              handleCallbackFunc={handleCallbackFunc}
            />
          </div>
        </Col>
      </Row>
    </div>
  )
}

export default React.memo(DoctorScheduleListFilter)
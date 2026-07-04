import React, {FC} from 'react'
import {Descriptions, Badge} from 'antd'
import {DateTimeUtils} from 'src/app/utils'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {EmployeeAction} from '../Actions/Employee.actions'

const cap = (val: any) => (val ? String(val).charAt(0).toUpperCase() + String(val).slice(1) : '—')
const val = (v: any) => v || '—'

const EmployeeView: FC<any> = ({itemData, handleCallbackFunc}) => {
  return (
    <div className='employee-view-container'>
      {/* Header */}
      <div className='d-flex align-items-start gap-4 mb-6 flex-wrap'>
        <div className='flex-grow-1'>
          <div className='d-flex align-items-center gap-3 flex-wrap'>
            <h4 className='mb-0 fw-bold'>{val(itemData.name_en)}</h4>
            <Badge
              status={itemData.status === 1 ? 'success' : 'error'}
              text={itemData.status === 1 ? 'Active' : 'Inactive'}
            />
          </div>
          {itemData.name_bn && (
            <div className='text-muted mt-1' style={{fontSize: 13}}>{itemData.name_bn}</div>
          )}
        </div>
        <div className='d-flex gap-2'>
          <EditAction
            entityId={itemData.id}
            actionItem={EmployeeAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={EmployeeAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>

      {/* Details */}
      <Descriptions bordered column={2} size='small'>
        <Descriptions.Item label='Employee ID'>{val(itemData.employee_id)}</Descriptions.Item>
        <Descriptions.Item label='Name (EN)'>{val(itemData.name_en)}</Descriptions.Item>
        <Descriptions.Item label='Name (BN)'>{val(itemData.name_bn)}</Descriptions.Item>
        <Descriptions.Item label='Designation'>{val(itemData.designation_name)}</Descriptions.Item>
        <Descriptions.Item label='Gender'>{cap(itemData.gender)}</Descriptions.Item>
        <Descriptions.Item label='Mobile'>{val(itemData.mobile)}</Descriptions.Item>
        <Descriptions.Item label='Date of Birth'>{val(itemData.dob)}</Descriptions.Item>
        <Descriptions.Item label='Joining Date'>
          {DateTimeUtils.formatDateTimeA(itemData.joining_date)}
        </Descriptions.Item>
        <Descriptions.Item label='Employee Type'>{val(itemData.employee_type)}</Descriptions.Item>
        <Descriptions.Item label='Employee Category'>{val(itemData.employee_category)}</Descriptions.Item>
        <Descriptions.Item label='Status'>
          <Badge
            status={itemData.status === 1 ? 'success' : 'error'}
            text={itemData.status === 1 ? 'Active' : 'Inactive'}
          />
        </Descriptions.Item>
        <Descriptions.Item label='Created At'>
          {DateTimeUtils.formatDateTimeA(itemData.created_at)}
        </Descriptions.Item>
      </Descriptions>
    </div>
  )
}

export default React.memo(EmployeeView)

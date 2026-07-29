import React, {FC} from 'react'
import {DateTimeUtils} from 'src/app/utils'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {ShiftHandoverAction as ACT} from '../Actions/ShiftHandover.actions'
import {StatusEnum} from 'src/app/utils/enums'

const ShiftHandoverView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction entityId={itemData.id} actionItem={ACT.COMMON_ACTION.EDIT} handleCallbackFunc={handleCallbackFunc} />
          <DeleteAction entityId={itemData.id} actionItem={ACT.COMMON_ACTION.DELETE} handleCallbackFunc={handleCallbackFunc} />
        </div>
      </div>
      <div className='table-responsive'>
        <table className='table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4'>
          <tr>
            <td width='25%'>Role</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.role_type ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Ward ID</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.ward_id ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Shift</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.shift_label ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Summary</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.summary ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>State</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.state ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Status</td><td width='5%'>:</td>
            <td width='70%'>{StatusEnum[itemData.status]}</td>
          </tr>
          <tr>
            <td width='25%'>Created</td><td width='5%'>:</td>
            <td width='70%'>{DateTimeUtils.formatDateTimeA(itemData.created_at)}</td>
          </tr>
        </table>
      </div>
    </div>
  )
}
export default React.memo(ShiftHandoverView)

import React, {FC} from 'react'
import {DateTimeUtils} from 'src/app/utils'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {SoapNoteAction as ACT} from '../Actions/SoapNote.actions'
import {StatusEnum} from 'src/app/utils/enums'

const SoapNoteView: FC<any> = (props) => {
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
            <td width='25%'>Patient ID</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.patient_id ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Subjective</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.subjective ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Objective</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.objective ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Assessment</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.assessment ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Plan</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.plan ?? '')}</td>
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
export default React.memo(SoapNoteView)

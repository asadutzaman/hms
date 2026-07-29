import React, {FC} from 'react'
import {DateTimeUtils} from 'src/app/utils'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {AtoeAssessmentAction as ACT} from '../Actions/AtoeAssessment.actions'
import {StatusEnum} from 'src/app/utils/enums'

const AtoeAssessmentView: FC<any> = (props) => {
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
            <td width='25%'>NEWS2</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.news2_score ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Airway</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.airway ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Breathing</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.breathing ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Circulation</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.circulation ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Disability</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.disability ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Exposure</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.exposure ?? '')}</td>
          </tr>
          <tr>
            <td width='25%'>Impression</td><td width='5%'>:</td>
            <td width='70%'>{String(itemData.impression ?? '')}</td>
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
export default React.memo(AtoeAssessmentView)

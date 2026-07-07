import React, {FC} from 'react'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {RadiologyTestAction} from '../Actions/RadiologyTest.actions'
import {useLang} from 'src/app/hooks/useLang'

const RadiologyTestView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction
            entityId={itemData.id}
            actionItem={RadiologyTestAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={RadiologyTestAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>
      <div className='table-responsive'>
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
              <td>{t('Modality')}</td>
              <td>:</td>
              <td>{(itemData.modality || '').toUpperCase()}</td>
            </tr>
            <tr>
              <td>{t('Body Part')}</td>
              <td>:</td>
              <td>{itemData.body_part}</td>
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
    </div>
  )
}
export default React.memo(RadiologyTestView)

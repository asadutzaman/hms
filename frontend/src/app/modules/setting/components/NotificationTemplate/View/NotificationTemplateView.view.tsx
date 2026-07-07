import React, {FC} from 'react'
import {DateTimeUtils} from 'src/app/utils'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {NotificationTemplateAction} from '../Actions/NotificationTemplate.actions'
import {useLang} from 'src/app/hooks/useLang'

const NotificationTemplateView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction
            entityId={itemData.id}
            actionItem={NotificationTemplateAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={NotificationTemplateAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>
      <div className='table-responsive'>
        <table className='table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4'>
          <tbody>
            <tr>
              <td width={'20%'}>{t('Key')}</td>
              <td width={'5%'}>:</td>
              <td width={'75%'}>{itemData.key}</td>
            </tr>
            <tr>
              <td>{t('Name')}</td>
              <td>:</td>
              <td>{itemData.name}</td>
            </tr>
            <tr>
              <td>{t('Channel')}</td>
              <td>:</td>
              <td>{itemData.channel}</td>
            </tr>
            <tr>
              <td>{t('Subject Template')}</td>
              <td>:</td>
              <td>{itemData.subject_template}</td>
            </tr>
            <tr>
              <td>{t('Body Template')}</td>
              <td>:</td>
              <td style={{whiteSpace: 'pre-wrap'}}>{itemData.body_template}</td>
            </tr>
            <tr>
              <td>{t('Active')}</td>
              <td>:</td>
              <td>{itemData.is_active ? t('Yes') : t('No')}</td>
            </tr>
            <tr>
              <td>{t('Created Time')}</td>
              <td>:</td>
              <td>{DateTimeUtils.formatDateTimeA(itemData.created_at)}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  )
}
export default React.memo(NotificationTemplateView)

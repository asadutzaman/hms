import React, {FC} from 'react'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {RadiologyReportTemplateAction} from '../Actions/RadiologyReportTemplate.actions'
import {useLang} from 'src/app/hooks/useLang'

const RadiologyReportTemplateView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction
            entityId={itemData.id}
            actionItem={RadiologyReportTemplateAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={RadiologyReportTemplateAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>
      <div className='table-responsive'>
        <table className='table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4'>
          <tbody>
            <tr>
              <td width={'20%'}>{t('Name')}</td>
              <td width={'5%'}>:</td>
              <td width={'75%'}>{itemData.name}</td>
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
              <td>{t('Findings Template')}</td>
              <td>:</td>
              <td style={{whiteSpace: 'pre-wrap'}}>{itemData.findings_template}</td>
            </tr>
            <tr>
              <td>{t('Impression Template')}</td>
              <td>:</td>
              <td style={{whiteSpace: 'pre-wrap'}}>{itemData.impression_template}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  )
}
export default React.memo(RadiologyReportTemplateView)

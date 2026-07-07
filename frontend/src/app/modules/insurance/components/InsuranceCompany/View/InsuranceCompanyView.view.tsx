import React, {FC} from 'react'
import {Tag} from 'antd'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {InsuranceCompanyAction} from '../Actions/InsuranceCompany.actions'
import {useLang} from 'src/app/hooks/useLang'
import InsuranceSchemePanel from './InsuranceSchemePanel'

const InsuranceCompanyView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction
            entityId={itemData.id}
            actionItem={InsuranceCompanyAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={InsuranceCompanyAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>
      <div className='table-responsive mb-8'>
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
              <td>{t('Type')}</td>
              <td>:</td>
              <td>
                <Tag color={itemData.tpa_type === 'corporate' ? 'purple' : 'blue'}>{itemData.tpa_type}</Tag>
              </td>
            </tr>
            <tr>
              <td>{t('Contact Person')}</td>
              <td>:</td>
              <td>{itemData.contact_person}</td>
            </tr>
            <tr>
              <td>{t('Phone')}</td>
              <td>:</td>
              <td>{itemData.phone}</td>
            </tr>
            <tr>
              <td>{t('Email')}</td>
              <td>:</td>
              <td>{itemData.email}</td>
            </tr>
            <tr>
              <td>{t('Credit Limit')}</td>
              <td>:</td>
              <td>{itemData.credit_limit}</td>
            </tr>
            <tr>
              <td>{t('Address')}</td>
              <td>:</td>
              <td>{itemData.address}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <InsuranceSchemePanel insuranceCompanyId={itemData.id} />
    </div>
  )
}
export default React.memo(InsuranceCompanyView)

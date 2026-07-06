import React, {FC} from 'react'
import {Tag} from 'antd'
import {DateTimeUtils} from 'src/app/utils'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {BedAction} from '../Actions/Bed.actions'
import {StatusEnum} from 'src/app/utils/enums'
import {useLang} from 'src/app/hooks/useLang'

const bedStatusColor = (status: string): string => {
  switch (status) {
    case 'vacant':
      return 'green'
    case 'occupied':
      return 'red'
    case 'reserved':
      return 'gold'
    case 'cleaning':
      return 'blue'
    case 'maintenance':
      return 'default'
    default:
      return 'default'
  }
}

const BedView: FC<any> = (props) => {
  const {itemData, handleCallbackFunc} = props
  const {t} = useLang()
  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction
            entityId={itemData.id}
            actionItem={BedAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={BedAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>
      <div className='table-responsive'>
        <table className='table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4'>
          <tr>
            <td width={'20%'}>{t('Bed Number')}</td>
            <td width={'5%'}>:</td>
            <td width={'75%'}>{itemData.bed_number}</td>
          </tr>
          <tr>
            <td width={'20%'}>{t('Ward')}</td>
            <td width={'5%'}>:</td>
            <td width={'75%'}>{itemData.ward_name}</td>
          </tr>
          <tr>
            <td width={'20%'}>{t('Bed Type')}</td>
            <td width={'5%'}>:</td>
            <td width={'75%'}>{itemData.bed_type}</td>
          </tr>
          <tr>
            <td width={'20%'}>{t('Daily Rate')}</td>
            <td width={'5%'}>:</td>
            <td width={'75%'}>{itemData.daily_rate}</td>
          </tr>
          <tr>
            <td width={'20%'}>{t('Bed Status')}</td>
            <td width={'5%'}>:</td>
            <td width={'75%'}>
              <Tag color={bedStatusColor(itemData.bed_status)} className='text-capitalize'>
                {itemData.bed_status}
              </Tag>
            </td>
          </tr>
          <tr>
            <td width={'20%'}>{t('Status')}</td>
            <td width={'5%'}>:</td>
            <td width={'75%'}>{StatusEnum[itemData.status]}</td>
          </tr>
          <tr>
            <td width={'20%'}>{t('Created Time')}</td>
            <td width={'5%'}>:</td>
            <td width={'75%'}>{DateTimeUtils.formatDateTimeA(itemData.created_at)}</td>
          </tr>
          <tr>
            <td width={'20%'}>{t('Updated Time')}</td>
            <td width={'5%'}>:</td>
            <td width={'75%'}>{DateTimeUtils.formatDateTimeA(itemData.updated_at)}</td>
          </tr>
        </table>
      </div>
    </div>
  )
}
export default React.memo(BedView)

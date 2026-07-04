import React, {FC} from 'react'
import {Tag} from 'antd'
import {useLang} from 'src/app/hooks/useLang'
import {DateTimeUtils} from 'src/app/utils'
import {StatusEnum} from 'src/app/utils/enums'

const DrugInfoViewTab: FC<any> = (props) => {
  const {itemData} = props
  const {t} = useLang()

  return (
    <div className='table-responsive'>
      <table className='table table-row-dashed table-row-gray-300 align-middle gs-0 gy-1'>
        <tr>
          <td width={'20%'}>{t('Item Code')}</td>
          <td width={'5%'}>:</td>
          <td width={'75%'}>{itemData.code}</td>
        </tr>
        <tr>
          <td>{t('Item Name')}</td>
          <td>:</td>
          <td>{itemData.name_en} ({itemData.name_bn})</td>
        </tr>
        <tr>
          <td>{t('Generic Name')}</td>
          <td>:</td>
          <td>{itemData.generic_name}</td>
        </tr>
        <tr>
          <td>{t('Brand Name')}</td>
          <td>:</td>
          <td>{itemData.brand_name || '-'}</td>
        </tr>
        <tr>
          <td>{t('Mapped Generic')}</td>
          <td>:</td>
          <td>{itemData.is_generic ? <Tag color='blue'>{t('This is a generic/reference drug')}</Tag> : itemData.generic_drug_name || '-'}</td>
        </tr>
        <tr>
          <td>{t('Strength')}</td>
          <td>:</td>
          <td>{itemData.strength || '-'}</td>
        </tr>
        <tr>
          <td>{t('Dosage Form')}</td>
          <td>:</td>
          <td className='text-capitalize'>{itemData.dosage_form}</td>
        </tr>
        <tr>
          <td>{t('HSN Code')}</td>
          <td>:</td>
          <td>{itemData.hsn_code || '-'}</td>
        </tr>
        <tr>
          <td>{t('Controlled Substance')}</td>
          <td>:</td>
          <td>
            {itemData.is_controlled ? (
              <Tag color='volcano'>{itemData.controlled_schedule || t('Yes')}</Tag>
            ) : (
              t('No')
            )}
          </td>
        </tr>
        <tr>
          <td>{t('Manufacturer / Brand')}</td>
          <td>:</td>
          <td>{itemData.brand_master_name}</td>
        </tr>
        <tr>
          <td>{t('Item Category')}</td>
          <td>:</td>
          <td>{itemData.item_category_name}</td>
        </tr>
        <tr>
          <td>{t('Base Unit')}</td>
          <td>:</td>
          <td>{itemData.base_unit_short_name}</td>
        </tr>
        <tr>
          <td>{t('Re-order Qty')}</td>
          <td>:</td>
          <td>{itemData.reorder_qty}</td>
        </tr>
        <tr>
          <td>{t('Description')}</td>
          <td>:</td>
          <td>{itemData.description || '-'}</td>
        </tr>
        <tr>
          <td>{t('Status')}</td>
          <td>:</td>
          <td>{StatusEnum[itemData.status]}</td>
        </tr>
        <tr>
          <td>{t('Created Time')}</td>
          <td>:</td>
          <td>{DateTimeUtils.formatDateTimeA(itemData.created_at)}</td>
        </tr>
        <tr>
          <td>{t('Updated Time')}</td>
          <td>:</td>
          <td>{DateTimeUtils.formatDateTimeA(itemData.updated_at)}</td>
        </tr>
      </table>
    </div>
  )
}

export default React.memo(DrugInfoViewTab)

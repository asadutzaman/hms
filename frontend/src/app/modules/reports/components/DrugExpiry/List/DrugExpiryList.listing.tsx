import {Card, Spin, Tag} from 'antd'
import React, {FC} from 'react'
import ReportHeader from 'src/app/components/Header/ReportHeader'
import {useLang} from 'src/app/hooks/useLang'

const getDaysLeftBadge = (daysLeft: number) => {
  if (daysLeft <= 30) return 'danger'
  if (daysLeft <= 60) return 'warning'
  return 'success'
}

const DrugExpiryListing: FC<any> = (props) => {
  const {loading, listData} = props
  const {t} = useLang()

  return (
    <div className='p-6'>
      <Card className='mt-2'>
        <Spin spinning={loading}>
          <div className='listing-page-content'>
            <ReportHeader title={t('Drug Expiry Report')} />

            <table className='table table-bordered table-row-gray-300 gs-2 gy-0'>
              <thead>
                <tr>
                  <th>{t('Serial No.')}</th>
                  <th>{t('Generic Name')}</th>
                  <th>{t('Brand Name')}</th>
                  <th>{t('Dosage Form')}</th>
                  <th>{t('Item Code')}</th>
                  <th>{t('Branch')}</th>
                  <th>{t('Balance Quantity')}</th>
                  <th>{t('Expiry Date')}</th>
                  <th>{t('Days Left')}</th>
                </tr>
              </thead>
              <tbody>
                {listData.length === 0 && (
                  <tr>
                    <td colSpan={9} align='center'>
                      {t('No data found!')}
                    </td>
                  </tr>
                )}
                {listData.map((itemData: any, index: number) => (
                  <tr key={itemData.id}>
                    <td align='center'>{index + 1}</td>
                    <td>{itemData.generic_name}</td>
                    <td>{itemData.brand_name || '-'}</td>
                    <td>{itemData.dosage_form}</td>
                    <td>{itemData.item_code}</td>
                    <td>{itemData.branch_name}</td>
                    <td>{itemData.balance_quantity}</td>
                    <td>{itemData.expire_date}</td>
                    <td>
                      <Tag color={getDaysLeftBadge(itemData.days_left)}>
                        {itemData.days_left} {t('days')}
                      </Tag>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </Spin>
      </Card>
    </div>
  )
}

export default React.memo(DrugExpiryListing)

import React, {FC} from 'react'
import {Button, Spin, Tabs} from 'antd'
import {useLang} from 'src/app/hooks/useLang'

const DrugExpiryListFilter: FC<any> = (props) => {
  const {bucket, setBucket, summary, loading, exportLoading, handleExport, handleRefresh} = props
  const {t} = useLang()

  const tabItems = [
    {key: '30', label: `${t('Expiring within 30 days')} (${summary?.['30'] || 0})`},
    {key: '60', label: `${t('31 - 60 days')} (${summary?.['60'] || 0})`},
    {key: '90', label: `${t('61 - 90 days')} (${summary?.['90'] || 0})`},
  ]

  return (
    <div className='p-6'>
      <div className='d-flex justify-content-between align-items-center flex-wrap'>
        <Tabs activeKey={bucket} onChange={setBucket} items={tabItems} />
        <div className='d-flex'>
          <Button className='me-3' onClick={handleRefresh} disabled={loading}>
            {t('Refresh')}
          </Button>
          <Button type='primary' onClick={handleExport} disabled={exportLoading}>
            <Spin spinning={exportLoading} style={{paddingRight: exportLoading ? 8 : 0}} />
            {t('Export as XLS')}
          </Button>
        </div>
      </div>
    </div>
  )
}

export default React.memo(DrugExpiryListFilter)

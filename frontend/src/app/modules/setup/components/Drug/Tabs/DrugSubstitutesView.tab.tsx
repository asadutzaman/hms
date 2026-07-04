import React, {FC, useEffect, useState} from 'react'
import {Table, Empty, Skeleton, Tag} from 'antd'
import {DrugApi} from 'src/app/api'
import {useLang} from 'src/app/hooks/useLang'

const DrugSubstitutesViewTab: FC<any> = (props) => {
  const {itemData} = props
  const {t} = useLang()
  const [loading, setLoading] = useState(false)
  const [substitutes, setSubstitutes] = useState<any[]>([])

  useEffect(() => {
    if (!itemData?.id) return
    let mounted = true
    setLoading(true)
    DrugApi.substitutes(itemData.id)
      .then((res: any) => mounted && setSubstitutes(res?.data?.data || res?.data || []))
      .catch(() => mounted && setSubstitutes([]))
      .finally(() => mounted && setLoading(false))
    return () => {
      mounted = false
    }
  }, [itemData?.id])

  if (loading) return <Skeleton active paragraph={{rows: 3}} />
  if (!substitutes.length) return <Empty description={t('No alternative brands or generic mapping found')} />

  const columns = [
    {
      title: t('Name'),
      dataIndex: 'brand_name',
      key: 'brand_name',
      render: (v: string, row: any) => (
        <span>
          {v || row.generic_name} {row.is_generic && <Tag color='blue'>{t('Generic')}</Tag>}
        </span>
      ),
    },
    {title: t('Generic Name'), dataIndex: 'generic_name', key: 'generic_name'},
    {title: t('Strength'), dataIndex: 'strength', key: 'strength', render: (v: string) => v || '-'},
    {
      title: t('Form'),
      dataIndex: 'dosage_form',
      key: 'dosage_form',
      render: (v: string) => <span className='text-capitalize'>{v}</span>,
    },
    {title: t('Manufacturer'), dataIndex: 'brand_master_name', key: 'brand_master_name'},
  ]

  return <Table rowKey='id' size='small' columns={columns} dataSource={substitutes} pagination={false} />
}

export default React.memo(DrugSubstitutesViewTab)

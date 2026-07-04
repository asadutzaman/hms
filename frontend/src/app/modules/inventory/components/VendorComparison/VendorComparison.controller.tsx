import React, {FC, useState} from 'react'
import {Button, Card, Col, Empty, Modal, Row, Skeleton, Table, Tag} from 'antd'
import {PlusOutlined, TrophyOutlined} from '@ant-design/icons'
import {VendorQuoteApi} from 'src/app/api'
import {Message} from 'src/app/utils'
import {useLang} from 'src/app/hooks/useLang'
import ItemSelect from 'src/app/components/Dropdown/LiveSearch/ItemSelect'
import AddQuoteModal from './AddQuoteModal'
import SelectWinnerModal from './SelectWinnerModal'

const VendorComparisonController: FC = () => {
  const {t} = useLang()
  const [itemIds, setItemIds] = useState<any[]>([])
  const [loading, setLoading] = useState(false)
  const [comparison, setComparison] = useState<any[]>([])
  const [addQuoteOpen, setAddQuoteOpen] = useState(false)
  const [winnerQuote, setWinnerQuote] = useState<any>(null)

  const loadComparison = (ids: any[]) => {
    if (!ids.length) {
      setComparison([])
      return
    }
    setLoading(true)
    VendorQuoteApi.compare({item_ids: ids})
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? {}
        setComparison(data.comparison || [])
      })
      .catch(() => Message.error('Failed to load comparison'))
      .finally(() => setLoading(false))
  }

  const handleItemSelect = (value: any) => {
    if (!value || itemIds.includes(value)) return
    const newIds = [...itemIds, value]
    setItemIds(newIds)
    loadComparison(newIds)
  }

  const removeItem = (id: any) => {
    const newIds = itemIds.filter((i) => i !== id)
    setItemIds(newIds)
    loadComparison(newIds)
  }

  const handleQuoteAdded = () => {
    setAddQuoteOpen(false)
    loadComparison(itemIds)
  }

  const handleWinnerSelected = () => {
    setWinnerQuote(null)
    loadComparison(itemIds)
  }

  const columns = [
    {title: t('Supplier'), dataIndex: 'supplier_name', key: 'supplier_name'},
    {title: t('Quoted Price'), dataIndex: 'quoted_unit_price', key: 'quoted_unit_price'},
    {title: t('Delivery Days'), dataIndex: 'quoted_delivery_days', key: 'quoted_delivery_days'},
    {title: t('Notes'), dataIndex: 'notes', key: 'notes', render: (v: string) => v || '-'},
    {
      title: t('Winner'),
      key: 'winner',
      render: (_: any, row: any) =>
        row.is_selected ? (
          <Tag color='gold' icon={<TrophyOutlined />}>
            {t('Selected')}
          </Tag>
        ) : (
          <Button size='small' onClick={() => setWinnerQuote(row)}>
            {t('Select as Winner')}
          </Button>
        ),
    },
  ]

  return (
    <div className='card p-6'>
      <Row gutter={[16, 16]} className='mb-4' align='middle'>
        <Col span={16}>
          <label className='form-label d-block'>{t('Items to Compare')}</label>
          <ItemSelect
            itemNameCode={null}
            placeholder={t('Search by Item Name/Code (type min 3 digit)')}
            onSelect={(value: any) => handleItemSelect(value)}
          />
          <div className='mt-2'>
            {itemIds.map((id) => {
              const found = comparison.find((c) => c.item_id === id)
              return (
                <Tag key={id} closable onClose={() => removeItem(id)} className='mb-1'>
                  {found?.item_name || `#${id}`}
                </Tag>
              )
            })}
          </div>
        </Col>
        <Col span={8} className='d-flex justify-content-end'>
          <Button type='primary' icon={<PlusOutlined />} onClick={() => setAddQuoteOpen(true)}>
            {t('Add Vendor Quote')}
          </Button>
        </Col>
      </Row>

      {loading ? (
        <Skeleton active paragraph={{rows: 4}} />
      ) : comparison.length === 0 ? (
        <Empty description={t('Select items above to compare vendor quotes')} />
      ) : (
        comparison.map((group) => (
          <Card
            key={group.item_id}
            title={`${group.item_code ? `[${group.item_code}] ` : ''}${group.item_name}`}
            className='mb-4'
          >
            <Table
              rowKey='id'
              size='small'
              columns={columns}
              dataSource={group.quotes}
              pagination={false}
            />
          </Card>
        ))
      )}

      <Modal
        title={t('Add Vendor Quote')}
        open={addQuoteOpen}
        onCancel={() => setAddQuoteOpen(false)}
        footer={null}
        destroyOnClose
      >
        <AddQuoteModal onSaved={handleQuoteAdded} />
      </Modal>

      <Modal
        title={t('Select Winning Quote')}
        open={!!winnerQuote}
        onCancel={() => setWinnerQuote(null)}
        footer={null}
        destroyOnClose
      >
        {winnerQuote && <SelectWinnerModal quote={winnerQuote} onSaved={handleWinnerSelected} />}
      </Modal>
    </div>
  )
}

export default VendorComparisonController

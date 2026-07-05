import React, {FC, useMemo, useState} from 'react'
import {Modal, Button, Table, InputNumber, Select, Tag, notification, Alert} from 'antd'
import {MedicineBoxOutlined} from '@ant-design/icons'
import {PrescriptionDispenseApi} from 'src/app/api'
import {useUserList} from 'src/app/hooks/lists/useUserList'

interface PrescriptionDispensePanelProps {
  prescriptionId: any
  items: any[]
  onDispensed?: () => void
}

// Reachable from OpdVisitView's prescription list — dispenses catalog-linked
// prescription items from the pharmacy counter's branch stock (FEFO), with a
// required witness for controlled drugs.
const PrescriptionDispensePanel: FC<PrescriptionDispensePanelProps> = ({
  prescriptionId,
  items,
  onDispensed,
}) => {
  const [open, setOpen] = useState(false)
  const [quantities, setQuantities] = useState<Record<number, number>>({})
  const [witnesses, setWitnesses] = useState<Record<number, number>>({})
  const [submitting, setSubmitting] = useState(false)
  const {userList} = useUserList()

  const dispensableItems = useMemo(() => items.filter((it) => !!it.drug_id), [items])

  const openModal = () => {
    setQuantities({})
    setWitnesses({})
    setOpen(true)
  }

  const handleDispense = async () => {
    const lines = dispensableItems
      .filter((it) => quantities[it.id] > 0)
      .map((it) => ({
        opd_prescription_item_id: it.id,
        dispensed_quantity: quantities[it.id],
        witnessed_by: witnesses[it.id] || undefined,
      }))

    if (!lines.length) {
      notification.warning({message: 'Enter a quantity to dispense for at least one item'})
      return
    }

    const missingWitness = lines.find((line) => {
      const item = dispensableItems.find((it) => it.id === line.opd_prescription_item_id)
      return item?.is_controlled && !line.witnessed_by
    })
    if (missingWitness) {
      notification.error({message: 'A witness is required for controlled drug items'})
      return
    }

    setSubmitting(true)
    try {
      await PrescriptionDispenseApi.dispense(prescriptionId, {items: lines})
      notification.success({message: 'Items dispensed successfully'})
      setOpen(false)
      onDispensed?.()
    } catch (err: any) {
      const shortfall = err?.response?.data?.data?.shortfall
      if (Array.isArray(shortfall) && shortfall.length) {
        notification.error({
          message: 'Insufficient stock',
          description: shortfall
            .map((s: any) => `${s.drug_name}: need ${s.requested_qty}, have ${s.available_qty}`)
            .join('; '),
        })
      } else {
        notification.error({message: err?.response?.data?.message || 'Failed to dispense items'})
      }
    } finally {
      setSubmitting(false)
    }
  }

  if (!dispensableItems.length) return null

  const columns = [
    {
      title: 'Drug',
      dataIndex: 'drug_name',
      render: (_: any, record: any) => (
        <>
          {record.drug_name}
          {record.is_controlled && (
            <Tag color='red' className='ms-2'>
              Controlled{record.controlled_schedule ? ` — ${record.controlled_schedule}` : ''}
            </Tag>
          )}
        </>
      ),
    },
    {title: 'Dose', dataIndex: 'dose_display'},
    {title: 'Already Dispensed', dataIndex: 'dispensed_quantity'},
    {
      title: 'Dispense Qty',
      dataIndex: 'dispense_qty',
      render: (_: any, record: any) => (
        <InputNumber
          min={0}
          value={quantities[record.id]}
          onChange={(val) => setQuantities((prev) => ({...prev, [record.id]: Number(val) || 0}))}
        />
      ),
    },
    {
      title: 'Witnessed By',
      dataIndex: 'witnessed_by',
      render: (_: any, record: any) =>
        record.is_controlled ? (
          <Select
            style={{minWidth: 160}}
            placeholder='Select witness'
            value={witnesses[record.id]}
            onChange={(val) => setWitnesses((prev) => ({...prev, [record.id]: val}))}
            options={userList.map((u: any) => ({label: u.name, value: u.id}))}
          />
        ) : (
          '-'
        ),
    },
  ]

  return (
    <>
      <Button icon={<MedicineBoxOutlined />} onClick={openModal}>
        Dispense
      </Button>
      <Modal
        title='Dispense Prescription'
        open={open}
        onCancel={() => setOpen(false)}
        onOk={handleDispense}
        confirmLoading={submitting}
        width={800}
        okText='Dispense'
      >
        <Alert
          type='info'
          showIcon
          className='mb-4'
          message='Items are dispensed from your session branch stock (FEFO — nearest expiry first).'
        />
        <Table
          rowKey='id'
          size='small'
          pagination={false}
          columns={columns}
          dataSource={dispensableItems}
        />
      </Modal>
    </>
  )
}

export default PrescriptionDispensePanel

import React, {FC, useEffect, useState} from 'react'
import {Button, Empty, Form, Input, Modal, Popconfirm, Select, Skeleton, Table, Upload} from 'antd'
import {DeleteOutlined, PlusOutlined, UploadOutlined, DownloadOutlined} from '@ant-design/icons'
import {PatientAttachmentApi} from 'src/app/api'
import {DateTimeUtils, Message} from 'src/app/utils'
import {CONSTANT_CONFIG} from 'src/app/constants'

const categoryOptions = [
  {value: 'lab_report', label: 'Lab Report'},
  {value: 'radiology_image', label: 'Radiology Image'},
  {value: 'consent_form', label: 'Consent Form'},
  {value: 'discharge_summary', label: 'Discharge Summary'},
  {value: 'insurance_document', label: 'Insurance Document'},
  {value: 'other', label: 'Other'},
]

interface PatientAttachmentPanelProps {
  patientId: any
  canEdit?: boolean
}

const PatientAttachmentPanel: FC<PatientAttachmentPanelProps> = ({patientId, canEdit = true}) => {
  const [loading, setLoading] = useState(false)
  const [attachments, setAttachments] = useState<any[]>([])
  const [modalOpen, setModalOpen] = useState(false)
  const [saving, setSaving] = useState(false)
  const [fileList, setFileList] = useState<any[]>([])
  const [form] = Form.useForm()

  const loadData = () => {
    if (!patientId) return
    setLoading(true)
    PatientAttachmentApi.byPatient(patientId)
      .then((res: any) => {
        const data = res?.data?.data ?? res?.data ?? []
        setAttachments(Array.isArray(data) ? data : [])
      })
      .catch(() => setAttachments([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadData()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [patientId])

  const handleAdd = () => {
    form.resetFields()
    form.setFieldsValue({category: 'other'})
    setFileList([])
    setModalOpen(true)
  }

  const handleSave = async () => {
    if (fileList.length === 0) {
      Message.error('Please select a file to upload')
      return
    }
    try {
      const values = await form.validateFields()
      setSaving(true)
      await PatientAttachmentApi.upload(fileList[0].originFileObj || fileList[0], {
        patient_id: patientId,
        category: values.category,
        title: values.title,
        description: values.description,
      })
      Message.success('Attachment uploaded')
      setModalOpen(false)
      loadData()
    } catch (err: any) {
      if (err?.errorFields) return
      Message.error(err?.response?.data?.message || 'Failed to upload attachment')
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = (id: any) => {
    PatientAttachmentApi.delete(id)
      .then(() => {
        Message.success('Attachment removed')
        loadData()
      })
      .catch(() => Message.error('Failed to remove attachment'))
  }

  const handleDownload = (row: any) => {
    const url = `${CONSTANT_CONFIG.API_SERVER_URL}/${row.file?.file_url}`
    window.open(url, '_blank')
  }

  const columns: any[] = [
    {title: 'Title', dataIndex: 'title', key: 'title'},
    {
      title: 'Category',
      dataIndex: 'category',
      key: 'category',
      render: (v: string) => categoryOptions.find((c) => c.value === v)?.label || v,
    },
    {title: 'File', dataIndex: ['file', 'original_filename'], key: 'file'},
    {
      title: 'Uploaded At',
      dataIndex: 'uploaded_at',
      key: 'uploaded_at',
      render: (v: string) => (v ? DateTimeUtils.formatDateTimeA(v) : '-'),
    },
    {
      title: 'Action',
      key: 'action',
      render: (_: any, row: any) => (
        <>
          <Button size='small' className='me-2' icon={<DownloadOutlined />} onClick={() => handleDownload(row)} />
          {canEdit && (
            <Popconfirm title='Remove this attachment?' onConfirm={() => handleDelete(row.id)}>
              <Button size='small' danger icon={<DeleteOutlined />} />
            </Popconfirm>
          )}
        </>
      ),
    },
  ]

  return (
    <div>
      <div className='d-flex justify-content-between align-items-center mb-2'>
        <h5 className='mb-0'>Attachments</h5>
        {canEdit && (
          <Button size='small' type='primary' icon={<PlusOutlined />} onClick={handleAdd}>
            Upload Attachment
          </Button>
        )}
      </div>

      {loading ? (
        <Skeleton active paragraph={{rows: 3}} />
      ) : attachments.length ? (
        <Table rowKey='id' size='small' columns={columns} dataSource={attachments} pagination={false} />
      ) : (
        <Empty description='No attachments uploaded yet' />
      )}

      <Modal
        title='Upload Attachment'
        open={modalOpen}
        onCancel={() => setModalOpen(false)}
        onOk={handleSave}
        confirmLoading={saving}
        destroyOnClose
      >
        <Form form={form} layout='vertical'>
          <Form.Item label='File' required>
            <Upload
              fileList={fileList}
              beforeUpload={() => false}
              onChange={(info) => setFileList(info.fileList.slice(-1))}
              maxCount={1}
            >
              <Button icon={<UploadOutlined />}>Select File</Button>
            </Upload>
          </Form.Item>
          <Form.Item name='category' label='Category' rules={[{required: true}]}>
            <Select options={categoryOptions} />
          </Form.Item>
          <Form.Item name='title' label='Title'>
            <Input placeholder='e.g. CBC Report - 2026-07-01' />
          </Form.Item>
          <Form.Item name='description' label='Description'>
            <Input.TextArea rows={3} />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  )
}

export default PatientAttachmentPanel

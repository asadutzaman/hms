import React, {useEffect} from 'react'
import {Empty, Select, Spin} from 'antd'
import {SelectProps} from 'antd/lib/select'
import {useDoctorList} from '../../hooks/lists/useDoctorList'

interface Props extends SelectProps {
  doctorId: any
  // When provided, only doctors of this department are listed.
  departmentId?: any
  placeholder?: string
  allowClear?: boolean

  onLoad?: (value: any) => void
  onChange?: (value: any, option: any) => void
  onSelect?: (value: any, option: any) => void
}

// Picker over users holding the Doctor role. Pass departmentId to make it a
// dependent (Department -> Doctor) select.
const DoctorSelect: React.FC<Props> = (props) => {
  const {Option} = Select
  const {doctorId, departmentId} = props

  const {doctorList, loadingDoctorList} = useDoctorList(departmentId)

  useEffect(() => {
    if (doctorId && doctorList.length && props.onLoad) {
      props.onLoad(doctorId)
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [doctorId, doctorList])

  return (
    <Select
      {...props}
      allowClear={props.allowClear ?? true}
      showSearch
      placeholder={props.placeholder || 'Select doctor'}
      value={doctorId ?? undefined}
      notFoundContent={loadingDoctorList ? <Spin size='small' /> : <Empty />}
      loading={loadingDoctorList}
      onChange={(value, option) => props.onChange?.(value, option)}
      onSelect={(value, option) => props.onSelect?.(value, option)}
      optionFilterProp='children'
      filterOption={(input, option: any) =>
        String(option?.children ?? '')
          .toLowerCase()
          .indexOf(input.toLowerCase()) >= 0
      }
    >
      {doctorList.map((doc: any) => (
        <Option key={`doctor-${doc.id}`} value={doc.id}>
          {doc.name}
        </Option>
      ))}
    </Select>
  )
}

export default DoctorSelect

import React, {useEffect} from 'react'
import {Empty, Select, Spin} from 'antd'
import {SelectProps} from 'antd/lib/select'
import {useWardList} from '../../hooks/lists/useWardList'

interface Props extends SelectProps {
  wardId?: any
  placeholder?: string
  isMultiple?: boolean

  onLoad?: (value: any) => void
  onChange?: (value: any, option: any) => void
  onSelect?: (value: any, option: any) => void
}

const WardSelect: React.FC<Props> = (props) => {
  const {Option} = Select
  const {wardId, isMultiple = false} = props

  const {loadingWardList, wardList} = useWardList()

  useEffect(() => {
    if (wardId && wardList.length) {
      if (props.onLoad) {
        props.onLoad(wardId)
      }
    }
  }, [wardId, wardList.length, props])

  const handleOnChanged = (value: any, option: any) => {
    if (props.onChange) {
      props.onChange(value, option)
    }
  }

  const handleOnSelect = (value: any, option: any) => {
    if (props.onSelect) {
      props.onSelect(value, option)
    }
  }

  // FOR MULTIPLE
  if (isMultiple) {
    return (
      <Select
        {...props}
        allowClear={true}
        showSearch
        placeholder={props.placeholder || '-- Select --'}
        notFoundContent={loadingWardList ? <Spin size='small' /> : <Empty />}
        onChange={(value, option) => handleOnChanged(value, option)}
        onSelect={(value, option) => handleOnSelect(value, option)}
        loading={loadingWardList}
        optionFilterProp='children'
        filterOption={(input, option: any) =>
          option?.children?.toLowerCase()?.indexOf(input.toLowerCase()) >= 0
        }
        mode='multiple'
      >
        {wardList &&
          wardList.map((item: any, index: any) => {
            return (
              <Option key={`ward-${index}`} value={item.id}>
                {item.name}
              </Option>
            )
          })}
      </Select>
    )
  } else {
    return (
      <Select
        {...props}
        allowClear={true}
        showSearch
        placeholder={props.placeholder || '-- Select --'}
        value={wardId}
        notFoundContent={loadingWardList ? <Spin size='small' /> : <Empty />}
        onChange={(value, option) => handleOnChanged(value, option)}
        onSelect={(value, option) => handleOnSelect(value, option)}
        loading={loadingWardList}
        optionFilterProp='children'
        filterOption={(input, option: any) =>
          option?.children?.toLowerCase()?.indexOf(input.toLowerCase()) >= 0
        }
      >
        {wardList &&
          wardList.map((item: any, index: any) => {
            return (
              <Option key={`ward-${index}`} value={item.id}>
                {item.name}
              </Option>
            )
          })}
      </Select>
    )
  }
}

export default WardSelect

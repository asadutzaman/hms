import React, {useEffect} from 'react'
import {Empty, Select, Spin} from 'antd'
import {SelectProps} from 'antd/lib/select'
import {useDrugList} from 'src/app/hooks/lists/useDrugList'

interface Props extends SelectProps {
  drugId: any
  placeholder?: string

  onLoad?: (value: any) => void
  onChange?: (value: any, option: any) => void
  onSelect?: (value: any, drug: any) => void
}

// Searchable picker over the pharmacy Drug catalog — selecting a drug here
// autofills the prescription line's generic/strength/dosage_form and links
// it (via drug_id) to stock for dispensing, interaction checks, and
// controlled-drug tracking.
const DrugSelect: React.FC<Props> = (props) => {
  const {Option} = Select
  const {drugId} = props

  const {drugList, loadingDrugList, getDrugById} = useDrugList()

  useEffect(() => {
    if (drugId && drugList.length) {
      if (props.onLoad) {
        props.onLoad(drugId)
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [drugId, drugList])

  const handleOnSelect = (value: any) => {
    if (props.onSelect) {
      props.onSelect(value, getDrugById(value))
    }
  }

  return (
    <Select
      {...props}
      allowClear={true}
      showSearch
      placeholder={props.placeholder || 'Search drug...'}
      value={drugId}
      notFoundContent={loadingDrugList ? <Spin size='small' /> : <Empty />}
      onChange={(value, option) => props.onChange?.(value, option)}
      onSelect={(value) => handleOnSelect(value)}
      loading={loadingDrugList}
      optionFilterProp='children'
      filterOption={(input, option: any) =>
        String(option?.children ?? '')
          .toLowerCase()
          .indexOf(input.toLowerCase()) >= 0
      }
    >
      {drugList.map((drug: any) => {
        const label =
          `${drug.generic_name}` +
          (drug.brand_name ? ` (${drug.brand_name})` : '') +
          (drug.strength ? ` - ${drug.strength}` : '') +
          (drug.is_controlled ? ' [Controlled]' : '')
        return (
          <Option key={`drug-${drug.id}`} value={drug.id}>
            {label}
          </Option>
        )
      })}
    </Select>
  )
}

export default DrugSelect

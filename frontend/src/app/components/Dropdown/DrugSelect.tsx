import React, {useEffect, useMemo, useState} from 'react'
import {Empty, Select, Spin} from 'antd'
import {SelectProps} from 'antd/lib/select'
import debounce from 'lodash/debounce'
import {useDrugList} from 'src/app/hooks/lists/useDrugList'
import {useDrugSearch} from 'src/app/hooks/lists/useDrugSearch'

interface Props extends SelectProps {
  drugId: any
  placeholder?: string
  // Server-side typeahead: fetch one page per search term instead of the
  // whole catalog. Use for forms with many DrugSelect rows (prescriptions).
  serverSearch?: boolean

  onLoad?: (value: any) => void
  onChange?: (value: any, option: any) => void
  onSelect?: (value: any, drug: any) => void
}

const drugLabel = (drug: any) =>
  `${drug.generic_name}` +
  (drug.brand_name ? ` (${drug.brand_name})` : '') +
  (drug.strength ? ` - ${drug.strength}` : '') +
  (drug.is_controlled ? ' [Controlled]' : '')

// Searchable picker over the pharmacy Drug catalog — selecting a drug here
// autofills the prescription line's generic/strength/dosage_form and links
// it (via drug_id) to stock for dispensing, interaction checks, and
// controlled-drug tracking.
const ClientDrugSelect: React.FC<Props> = (props) => {
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
      {drugList.map((drug: any) => (
        <Option key={`drug-${drug.id}`} value={drug.id}>
          {drugLabel(drug)}
        </Option>
      ))}
    </Select>
  )
}

const ServerDrugSelect: React.FC<Props> = (props) => {
  const {Option} = Select
  const {drugId} = props

  const {drugList, loadingDrugList, searchDrugs, getDrugById, fetchDrugById} = useDrugSearch()
  const [selectedDrug, setSelectedDrug] = useState<any>(null)
  const [opened, setOpened] = useState<boolean>(false)

  // Resolve a pre-selected drug (edit mode) so its label renders and onLoad
  // fires, matching the client-mode contract.
  useEffect(() => {
    if (!drugId) {
      return
    }
    const drug = getDrugById(drugId)
    if (drug) {
      setSelectedDrug(drug)
      props.onLoad?.(drugId)
      return
    }
    fetchDrugById(drugId)
      .then((fetched) => {
        if (fetched) {
          setSelectedDrug(fetched)
          props.onLoad?.(drugId)
        }
      })
      .catch(() => {})
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [drugId])

  const debouncedSearch = useMemo(
    () =>
      debounce((term: string) => {
        searchDrugs(term).catch(() => {})
      }, 400),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  )

  useEffect(() => () => debouncedSearch.cancel(), [debouncedSearch])

  const handleDropdownVisibleChange = (open: boolean) => {
    if (open && !opened) {
      setOpened(true)
      searchDrugs('').catch(() => {})
    }
  }

  const handleOnSelect = (value: any) => {
    const drug = getDrugById(value)
    setSelectedDrug(drug ?? null)
    if (props.onSelect) {
      props.onSelect(value, drug)
    }
  }

  // Keep the selected drug visible even when the current page doesn't contain it.
  const options = useMemo(() => {
    if (selectedDrug && !drugList.some((d: any) => Number(d.id) === Number(selectedDrug.id))) {
      return [selectedDrug, ...drugList]
    }
    return drugList
  }, [drugList, selectedDrug])

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
      filterOption={false}
      onSearch={debouncedSearch}
      onDropdownVisibleChange={handleDropdownVisibleChange}
    >
      {options.map((drug: any) => (
        <Option key={`drug-${drug.id}`} value={drug.id}>
          {drugLabel(drug)}
        </Option>
      ))}
    </Select>
  )
}

const DrugSelect: React.FC<Props> = ({serverSearch = false, ...props}) => {
  return serverSearch ? <ServerDrugSelect {...props} /> : <ClientDrugSelect {...props} />
}

export default DrugSelect

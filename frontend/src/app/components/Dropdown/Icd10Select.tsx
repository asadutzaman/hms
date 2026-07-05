import React, {useState} from 'react'
import {Select, Spin} from 'antd'
import {SelectProps} from 'antd/lib/select'
import {Icd10CodeApi} from 'src/app/api'

interface Props extends SelectProps {
  icd10Id: any
  placeholder?: string
  onSelect?: (value: any, icd10: any) => void
}

// Typeahead over the seeded ICD-10 reference table — free text on
// diagnosis_code/diagnosis_description stays editable as a fallback for
// codes not yet in the seeded set.
const Icd10Select: React.FC<Props> = (props) => {
  const {Option} = Select
  const {icd10Id} = props
  const [options, setOptions] = useState<any[]>([])
  const [loading, setLoading] = useState(false)

  const handleSearch = (q: string) => {
    if (!q || q.length < 2) {
      setOptions([])
      return
    }
    setLoading(true)
    Icd10CodeApi.search(q)
      .then((res: any) => {
        const list = res?.data?.data ?? res?.data ?? []
        setOptions(Array.isArray(list) ? list : [])
      })
      .catch(() => setOptions([]))
      .finally(() => setLoading(false))
  }

  const handleSelect = (value: any) => {
    const icd10 = options.find((o) => o.id === value)
    props.onSelect?.(value, icd10)
  }

  return (
    <Select
      {...props}
      allowClear
      showSearch
      value={icd10Id}
      placeholder={props.placeholder || 'Search ICD-10 code or description...'}
      filterOption={false}
      notFoundContent={loading ? <Spin size='small' /> : null}
      onSearch={handleSearch}
      onChange={(value, option) => props.onChange?.(value, option)}
      onSelect={handleSelect}
    >
      {options.map((icd) => (
        <Option key={`icd10-${icd.id}`} value={icd.id}>
          {icd.code} — {icd.description}
        </Option>
      ))}
    </Select>
  )
}

export default Icd10Select

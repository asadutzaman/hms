import {useRef, useState} from 'react'
import {DrugApi} from '../../api'

const DRUG_SELECT_FIELDS =
  'id,item_id,generic_name,brand_name,strength,dosage_form,is_controlled,controlled_schedule'
const PAGE_SIZE = 20

// Server-side typeahead over the Drug catalog: fetches at most one page per
// search term instead of loading the whole formulary like useDrugList.
export const useDrugSearch = () => {
  const [drugList, setDrugList] = useState<any[]>([])
  const [loadingDrugList, setLoadingDrugList] = useState<boolean>(false)
  const fetchRef = useRef(0)
  // Drugs seen across pages (or fetched by id) so getDrugById can resolve a
  // selection even after the visible page has changed.
  const seenDrugsRef = useRef<Map<number, any>>(new Map())

  const searchDrugs = (term: string = ''): Promise<any> => {
    fetchRef.current += 1
    const fetchId = fetchRef.current
    setLoadingDrugList(true)

    const payload: any = {
      $select: DRUG_SELECT_FIELDS,
      $orderby: 'generic_name asc',
      $top: PAGE_SIZE,
    }
    if (term && term.trim()) {
      payload.$search = term.trim()
    }

    return new Promise((resolve, reject) => {
      DrugApi.dropdown(payload)
        .then((res) => {
          if (fetchId !== fetchRef.current) {
            resolve(res.data)
            return
          }
          const list = res?.data?.results ?? []
          list.forEach((drug: any) => seenDrugsRef.current.set(Number(drug.id), drug))
          setDrugList(list)
          setLoadingDrugList(false)
          resolve(res.data)
        })
        .catch((err) => {
          if (fetchId === fetchRef.current) {
            setLoadingDrugList(false)
          }
          reject(err)
        })
    })
  }

  const getDrugById = (id: any) => {
    return seenDrugsRef.current.get(Number(id))
  }

  // For pre-selected values (edit mode) whose drug is not in the current page.
  const fetchDrugById = (id: any): Promise<any> => {
    const cached = seenDrugsRef.current.get(Number(id))
    if (cached) {
      return Promise.resolve(cached)
    }
    return DrugApi.getById(id).then((res) => {
      const drug = res?.data?.data ?? res?.data
      if (drug?.id) {
        seenDrugsRef.current.set(Number(drug.id), drug)
      }
      return drug
    })
  }

  return {
    drugList,
    loadingDrugList,
    searchDrugs,
    getDrugById,
    fetchDrugById,
  }
}

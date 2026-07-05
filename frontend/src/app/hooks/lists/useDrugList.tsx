import {useState, useEffect} from 'react'
import {DrugApi} from '../../api'

export const useDrugList = () => {
  const [drugList, setDrugList] = useState<any>([])
  const [loadingDrugList, setLoadingDrugList] = useState<boolean>(false)

  useEffect(() => {
    loadDrugList()
  }, [])

  const loadDrugList = (): Promise<any> => {
    return new Promise((resolve, reject) => {
      setLoadingDrugList(true)
      const payload = {
        $select: 'id,item_id,generic_name,brand_name,strength,dosage_form,is_controlled,controlled_schedule',
        $orderby: 'generic_name asc',
      }
      DrugApi.dropdown(payload)
        .then((res) => {
          const list = res.data.results
          if (list.length > 0) {
            setDrugList(list)
          }
          setLoadingDrugList(false)
          resolve(res.data)
        })
        .catch((err) => {
          setLoadingDrugList(false)
          reject(err)
        })
    })
  }

  const getDrugById = (id: any) => {
    if (!drugList) {
      return
    }
    return drugList.find((item: any) => item.id === Number(id))
  }

  return {
    loadingDrugList,
    drugList,
    getDrugById,
  }
}

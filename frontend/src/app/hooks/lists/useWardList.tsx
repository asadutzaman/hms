import {useState, useEffect} from 'react'
import {WardApi} from 'src/app/api'

export const useWardList = () => {
  // USED STATES
  const [wardList, setWardList] = useState<any>([])
  const [loadingWardList, setLoadingWardList] = useState<boolean>(false)
  const [disabledWardList, setDisabledWardList] = useState<boolean>(true)

  useEffect(() => {
    loadWardList()
  }, [])

  const loadWardList = (): Promise<any> => {
    return new Promise((resolve, reject) => {
      setLoadingWardList(true)
      const payload = {
        $select: 'id,name,branch_id,ward_type,status',
        $orderby: 'name asc',
      }
      WardApi.dropdown(payload)
        .then((res) => {
          const list = res.data.results
          if (list.length > 0) {
            setWardList(list)
          }
          setLoadingWardList(false)
          resolve(res.data)
        })
        .catch((err) => {
          setLoadingWardList(false)
          reject(err)
        })
    })
  }

  const getWardById = (id: any) => {
    if (!wardList) {
      return
    }
    return wardList.find((item: any) => item.id === Number(id))
  }

  const setWardFormFieldValue = (formRef: any, key: any, value: any) => {
    if (wardList?.find((item: any) => item.id === Number(value))) {
      formRef.setFieldsValue({[key]: value})
    } else {
      formRef.setFieldsValue({[key]: null})
    }
  }

  return {
    wardList,
    loadingWardList,
    setWardFormFieldValue,
    getWardById,
    disabledWardList,
    setDisabledWardList,
  }
}

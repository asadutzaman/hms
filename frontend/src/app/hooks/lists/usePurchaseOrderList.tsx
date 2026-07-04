import {useState, useEffect} from 'react'
import {PurchaseOrderApi} from '../../api'

export const usePurchaseOrderList = () => {
  const [purchaseOrderList, setPurchaseOrderList] = useState<any>([])
  const [loadingPurchaseOrderList, setLoadingPurchaseOrderList] = useState<boolean>(false)

  useEffect(() => {
    loadPurchaseOrderList()
  }, [])

  const loadPurchaseOrderList = (): Promise<any> => {
    return new Promise((resolve, reject) => {
      setLoadingPurchaseOrderList(true)
      const payload = {
        $select: 'id,po_number,supplier_id,po_status',
        $orderby: 'id desc',
      }
      PurchaseOrderApi.dropdown(payload)
        .then((res) => {
          const list = res.data.results
          if (list.length > 0) {
            setPurchaseOrderList(list)
          }
          setLoadingPurchaseOrderList(false)
          resolve(res.data)
        })
        .catch((err) => {
          setLoadingPurchaseOrderList(false)
          reject(err)
        })
    })
  }

  const getPurchaseOrderById = (id: any) => {
    if (!purchaseOrderList) {
      return
    }
    return purchaseOrderList.find((item: any) => item.id === Number(id))
  }

  return {
    loadingPurchaseOrderList,
    purchaseOrderList,
    getPurchaseOrderById,
  }
}

import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/lab-sample`

const endpoints = {
    byOrder: (orderId: any) => `${RESOURCE_ENDPOINT}/by-order/${orderId}`,
    collect: () => `${RESOURCE_ENDPOINT}/collect`,
    receiveByBarcode: () => `${RESOURCE_ENDPOINT}/receive-by-barcode`,
    reject: (id: any) => `${RESOURCE_ENDPOINT}/${id}/reject`,
}

export default class LabSampleApi {
    public byOrder = (orderId: any): AxiosPromise<any> =>
        HttpService.get(endpoints.byOrder(orderId))

    public collect = (payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.collect(), payload)

    public receiveByBarcode = (payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.receiveByBarcode(), payload)

    public reject = (id: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.reject(id), payload)
}

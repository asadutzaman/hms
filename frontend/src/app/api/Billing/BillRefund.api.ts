import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/bill-refund`

const endpoints = {
    list: () => `${RESOURCE_ENDPOINT}`,
    getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    pending: () => `${RESOURCE_ENDPOINT}/pending`,
    byBill: () => `${RESOURCE_ENDPOINT}/by-bill`,
    request: () => `${RESOURCE_ENDPOINT}/request`,
    approve: (id: any) => `${RESOURCE_ENDPOINT}/${id}/approve`,
    reject: (id: any) => `${RESOURCE_ENDPOINT}/${id}/reject`,
}

export default class BillRefundApi {
    public list = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.list(), params, headers)

    public getById = (id: any): AxiosPromise<any> =>
        HttpService.get(endpoints.getById(id))

    public pending = (): AxiosPromise<any> => HttpService.get(endpoints.pending())

    public byBill = (billableType: string, billableId: any): AxiosPromise<any> =>
        HttpService.get(endpoints.byBill(), {billable_type: billableType, billable_id: billableId})

    public request = (payload = {}): AxiosPromise<any> => HttpService.post(endpoints.request(), payload)

    public approve = (id: any): AxiosPromise<any> => HttpService.post(endpoints.approve(id), {})

    public reject = (id: any, payload = {}): AxiosPromise<any> => HttpService.post(endpoints.reject(id), payload)
}

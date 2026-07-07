import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/lab-result`

const endpoints = {
    byOrderItem: (orderItemId: any) => `${RESOURCE_ENDPOINT}/by-order-item/${orderItemId}`,
    enter: (orderItemId: any) => `${RESOURCE_ENDPOINT}/enter/${orderItemId}`,
    verify: (orderItemId: any) => `${RESOURCE_ENDPOINT}/verify/${orderItemId}`,
}

export default class LabResultApi {
    public byOrderItem = (orderItemId: any): AxiosPromise<any> =>
        HttpService.get(endpoints.byOrderItem(orderItemId))

    public enter = (orderItemId: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.enter(orderItemId), payload)

    public verify = (orderItemId: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.verify(orderItemId), payload)
}

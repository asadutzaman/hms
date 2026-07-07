import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/radiology-report`

const endpoints = {
    byOrderItem: (orderItemId: any) => `${RESOURCE_ENDPOINT}/by-order-item/${orderItemId}`,
    saveDraft: (orderItemId: any) => `${RESOURCE_ENDPOINT}/save-draft/${orderItemId}`,
    finalize: (orderItemId: any) => `${RESOURCE_ENDPOINT}/finalize/${orderItemId}`,
    verify: (orderItemId: any) => `${RESOURCE_ENDPOINT}/verify/${orderItemId}`,
}

export default class RadiologyReportApi {
    public byOrderItem = (orderItemId: any): AxiosPromise<any> =>
        HttpService.get(endpoints.byOrderItem(orderItemId))

    public saveDraft = (orderItemId: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.saveDraft(orderItemId), payload)

    public finalize = (orderItemId: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.finalize(orderItemId), payload)

    public verify = (orderItemId: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.verify(orderItemId), payload)
}

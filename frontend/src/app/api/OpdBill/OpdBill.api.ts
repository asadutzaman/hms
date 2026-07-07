import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/opd-bill`

const endpoints = {
    list: () => `${RESOURCE_ENDPOINT}`,
    getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    create: () => `${RESOURCE_ENDPOINT}`,
    update: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    updatePartial: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    delete: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    bulk: () => `${RESOURCE_ENDPOINT}/bulk`,
    dropdown: () => `${RESOURCE_ENDPOINT}/dropdown`,
    generate: (id: any) => `${RESOURCE_ENDPOINT}/generate/${id}`,
    recordPayment: (id: any) => `${RESOURCE_ENDPOINT}/${id}/payment`,
    recordSplitPayment: (id: any) => `${RESOURCE_ENDPOINT}/${id}/payments`,
    waive: (id: any) => `${RESOURCE_ENDPOINT}/${id}/waive`,
    applyDiscount: (id: any) => `${RESOURCE_ENDPOINT}/${id}/discount`,
    applyPackage: (id: any) => `${RESOURCE_ENDPOINT}/${id}/apply-package`,
    approveDiscount: (id: any) => `${RESOURCE_ENDPOINT}/${id}/discount/approve`,
    rejectDiscount: (id: any) => `${RESOURCE_ENDPOINT}/${id}/discount/reject`,
    byVisit: (id: any) => `${RESOURCE_ENDPOINT}/by-visit/${id}`,
    print: (id: any) => `${RESOURCE_ENDPOINT}/${id}/print`,
    receiptPdf: (id: any) => `${RESOURCE_ENDPOINT}/${id}/receipt-pdf`,
}

export default class OpdBillApi {
    public list = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.list(), params, headers)

    public getById = (id: any): AxiosPromise<any> =>
        HttpService.get(endpoints.getById(id))

    public create = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.create(), payload, params, headers)

    public update = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.put(endpoints.update(id), payload, params, headers)

    public updatePartial = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.patch(endpoints.updatePartial(id), payload, params, headers)

    public delete = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.delete(endpoints.delete(id), params, headers)

    public bulk = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.bulk(), payload, params, headers)

    public dropdown = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.dropdown(), params, headers)

    public generate = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.generate(id), payload, params, headers)

    public recordPayment = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.recordPayment(id), payload, params, headers)

    public recordSplitPayment = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.recordSplitPayment(id), payload, params, headers)

    public waive = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.waive(id), payload, params, headers)

    public applyDiscount = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.applyDiscount(id), payload, params, headers)

    public approveDiscount = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.approveDiscount(id), {}, params, headers)

    public rejectDiscount = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.rejectDiscount(id), payload, params, headers)

    public byVisit = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.byVisit(id), params, headers)

    public print = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.print(id), params, headers)

    public receiptPdf = (id: any): AxiosPromise<any> =>
        HttpService.get(endpoints.receiptPdf(id), {}, {}, 'blob')

    public applyPackage = (id: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.applyPackage(id), payload)
}
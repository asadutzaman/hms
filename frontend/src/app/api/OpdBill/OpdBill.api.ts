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
    waive: (id: any) => `${RESOURCE_ENDPOINT}/${id}/waive`,
    byVisit: (id: any) => `${RESOURCE_ENDPOINT}/by-visit/${id}`,
    print: (id: any) => `${RESOURCE_ENDPOINT}/${id}/print`,
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

    public waive = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.waive(id), payload, params, headers)

    public byVisit = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.byVisit(id), params, headers)

    public print = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.print(id), params, headers)
}
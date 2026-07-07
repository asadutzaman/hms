import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/pre-authorization`

const endpoints = {
    list: () => `${RESOURCE_ENDPOINT}`,
    getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    create: () => `${RESOURCE_ENDPOINT}`,
    bulk: () => `${RESOURCE_ENDPOINT}/bulk`,
    dropdown: () => `${RESOURCE_ENDPOINT}/dropdown`,
    pending: () => `${RESOURCE_ENDPOINT}/pending`,
    byPatient: (patientId: any) => `${RESOURCE_ENDPOINT}/by-patient/${patientId}`,
    underReview: (id: any) => `${RESOURCE_ENDPOINT}/${id}/under-review`,
    approve: (id: any) => `${RESOURCE_ENDPOINT}/${id}/approve`,
    reject: (id: any) => `${RESOURCE_ENDPOINT}/${id}/reject`,
}

export default class PreAuthorizationApi {
    public list = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.list(), params, headers)

    public getById = (id: any): AxiosPromise<any> =>
        HttpService.get(endpoints.getById(id))

    public create = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.create(), payload, params, headers)

    public bulk = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.bulk(), payload, params, headers)

    public dropdown = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.dropdown(), params, headers)

    public pending = (): AxiosPromise<any> => HttpService.get(endpoints.pending())

    public byPatient = (patientId: any): AxiosPromise<any> => HttpService.get(endpoints.byPatient(patientId))

    public markUnderReview = (id: any): AxiosPromise<any> => HttpService.post(endpoints.underReview(id), {})

    public approve = (id: any, payload = {}): AxiosPromise<any> => HttpService.post(endpoints.approve(id), payload)

    public reject = (id: any, payload = {}): AxiosPromise<any> => HttpService.post(endpoints.reject(id), payload)
}

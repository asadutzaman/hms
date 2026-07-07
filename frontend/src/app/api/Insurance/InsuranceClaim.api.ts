import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/insurance-claim`

const endpoints = {
    list: () => `${RESOURCE_ENDPOINT}`,
    getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    create: () => `${RESOURCE_ENDPOINT}`,
    bulk: () => `${RESOURCE_ENDPOINT}/bulk`,
    dropdown: () => `${RESOURCE_ENDPOINT}/dropdown`,
    byPatient: (patientId: any) => `${RESOURCE_ENDPOINT}/by-patient/${patientId}`,
    byBill: () => `${RESOURCE_ENDPOINT}/by-bill`,
    submit: (id: any) => `${RESOURCE_ENDPOINT}/${id}/submit`,
    updateStatus: (id: any) => `${RESOURCE_ENDPOINT}/${id}/status`,
}

export default class InsuranceClaimApi {
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

    public byPatient = (patientId: any): AxiosPromise<any> => HttpService.get(endpoints.byPatient(patientId))

    public byBill = (billableType: string, billableId: any): AxiosPromise<any> =>
        HttpService.get(endpoints.byBill(), {billable_type: billableType, billable_id: billableId})

    public submit = (id: any): AxiosPromise<any> => HttpService.post(endpoints.submit(id), {})

    public updateStatus = (id: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.updateStatus(id), payload)
}

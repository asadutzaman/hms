import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/radiology-order`

const endpoints = {
    list: () => `${RESOURCE_ENDPOINT}`,
    getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    create: () => `${RESOURCE_ENDPOINT}`,
    updatePartial: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    bulk: () => `${RESOURCE_ENDPOINT}/bulk`,
    dropdown: () => `${RESOURCE_ENDPOINT}/dropdown`,
    worklist: () => `${RESOURCE_ENDPOINT}/worklist`,
    byPatient: (patientId: any) => `${RESOURCE_ENDPOINT}/by-patient/${patientId}`,
    byOpdVisit: (opdVisitId: any) => `${RESOURCE_ENDPOINT}/by-opd-visit/${opdVisitId}`,
    byIpdAdmission: (admissionId: any) => `${RESOURCE_ENDPOINT}/by-ipd-admission/${admissionId}`,
    cancel: (id: any) => `${RESOURCE_ENDPOINT}/${id}/cancel`,
    reportPdf: (id: any) => `${RESOURCE_ENDPOINT}/${id}/report-pdf`,
    markReported: (id: any) => `${RESOURCE_ENDPOINT}/${id}/mark-reported`,
}

export default class RadiologyOrderApi {
    public list = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.list(), params, headers)

    public getById = (id: any): AxiosPromise<any> =>
        HttpService.get(endpoints.getById(id))

    public create = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.create(), payload, params, headers)

    public updatePartial = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.patch(endpoints.updatePartial(id), payload, params, headers)

    public bulk = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.bulk(), payload, params, headers)

    public dropdown = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.dropdown(), params, headers)

    public worklist = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.worklist(), params, headers)

    public byPatient = (patientId: any, params = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.byPatient(patientId), params)

    public byOpdVisit = (opdVisitId: any, params = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.byOpdVisit(opdVisitId), params)

    public byIpdAdmission = (admissionId: any, params = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.byIpdAdmission(admissionId), params)

    public cancel = (id: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.cancel(id), payload)

    public reportPdf = (id: any): AxiosPromise<any> =>
        HttpService.get(endpoints.reportPdf(id), {}, {}, 'blob')

    public markReported = (id: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.markReported(id), payload)
}

import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/appointment`

const endpoints = {
    // standard CRUD
    list: () => `${RESOURCE_ENDPOINT}`,
    getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    getByCode: (code: string) => `${RESOURCE_ENDPOINT}/by-code/${code}`,
    create: () => `${RESOURCE_ENDPOINT}`,
    update: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    updatePartial: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    delete: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    bulk: () => `${RESOURCE_ENDPOINT}/bulk`,
    dropdown: () => `${RESOURCE_ENDPOINT}/dropdown`,
    getByWhere: () => `${RESOURCE_ENDPOINT}/get-by-where`,

    // booking lifecycle
    availableSlots: () => `${RESOURCE_ENDPOINT}/available-slots`,
    book: () => `${RESOURCE_ENDPOINT}/book`,
    reschedule: (id: any) => `${RESOURCE_ENDPOINT}/reschedule/${id}`,
    cancel: (id: any) => `${RESOURCE_ENDPOINT}/cancel/${id}`,
    confirm: (id: any) => `${RESOURCE_ENDPOINT}/confirm/${id}`,
    checkIn: (id: any) => `${RESOURCE_ENDPOINT}/check-in/${id}`,
    walkIn: () => `${RESOURCE_ENDPOINT}/walk-in`,
    startConsultation: (id: any) => `${RESOURCE_ENDPOINT}/start-consultation/${id}`,
    complete: (id: any) => `${RESOURCE_ENDPOINT}/complete/${id}`,
    markNoShow: (id: any) => `${RESOURCE_ENDPOINT}/no-show/${id}`,
    startBreak: (id: any) => `${RESOURCE_ENDPOINT}/start-break/${id}`,
    endBreak: (id: any) => `${RESOURCE_ENDPOINT}/end-break/${id}`,

    // ops board
    queue: () => `${RESOURCE_ENDPOINT}/queue`,
    dashboard: () => `${RESOURCE_ENDPOINT}/dashboard`,

    // audit
    auditLog: (id: any) => `${RESOURCE_ENDPOINT}/audit-log/${id}`,

    // stats
    stats: () => `${RESOURCE_ENDPOINT}/stats`,
}

export default class AppointmentApi {
    // ----- standard CRUD -----
    public list = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.list(), params, headers)

    public getById = (id: any): AxiosPromise<any> =>
        HttpService.get(endpoints.getById(id))

    public getByCode = (code: string): AxiosPromise<any> =>
        HttpService.get(endpoints.getByCode(code))

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

    public getByWhere = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.getByWhere(), params, headers)

    // ----- booking lifecycle -----
    public availableSlots = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.availableSlots(), payload, params, headers)

    public book = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.book(), payload, params, headers)

    public reschedule = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.reschedule(id), payload, params, headers)

    public cancel = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.cancel(id), payload, params, headers)

    public confirm = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.confirm(id), {}, params, headers)

    public checkIn = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.checkIn(id), payload, params, headers)

    public walkIn = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.walkIn(), payload, params, headers)

    public startConsultation = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.startConsultation(id), {}, params, headers)

    public complete = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.complete(id), payload, params, headers)

    public markNoShow = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.markNoShow(id), payload, params, headers)

    public startBreak = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.startBreak(id), {}, params, headers)

    public endBreak = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.endBreak(id), {}, params, headers)

    // ----- ops board -----
    public queue = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.queue(), payload, params, headers)

    public dashboard = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.dashboard(), payload, params, headers)

    // ----- audit -----
    public auditLog = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.auditLog(id), params, headers)

    // ----- stats -----
    public stats = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.stats(), payload, params, headers)
}

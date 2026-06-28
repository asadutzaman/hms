import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/appointment-waitlist`

const endpoints = {
    // standard CRUD
    list: () => `${RESOURCE_ENDPOINT}`,
    getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    create: () => `${RESOURCE_ENDPOINT}`,
    update: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    updatePartial: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    delete: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    bulk: () => `${RESOURCE_ENDPOINT}/bulk`,
    dropdown: () => `${RESOURCE_ENDPOINT}/dropdown`,
    getByWhere: () => `${RESOURCE_ENDPOINT}/get-by-where`,

    // waitlist ops
    active: () => `${RESOURCE_ENDPOINT}/active`,
    notify: (id: any) => `${RESOURCE_ENDPOINT}/notify/${id}`,
    notifyAll: () => `${RESOURCE_ENDPOINT}/notify-all`,
    convert: (id: any) => `${RESOURCE_ENDPOINT}/convert/${id}`,
    cancel: (id: any) => `${RESOURCE_ENDPOINT}/cancel/${id}`,
    expire: () => `${RESOURCE_ENDPOINT}/expire-stale`,
    reorder: (id: any) => `${RESOURCE_ENDPOINT}/reorder/${id}`,
}

export default class AppointmentWaitlistApi {
    // ----- standard CRUD -----
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

    public getByWhere = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.getByWhere(), params, headers)

    // ----- waitlist ops -----
    public active = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.active(), payload, params, headers)

    public notify = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.notify(id), payload, params, headers)

    public notifyAll = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.notifyAll(), payload, params, headers)

    public convert = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.convert(id), payload, params, headers)

    public cancel = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.cancel(id), payload, params, headers)

    public expire = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.expire(), {}, params, headers)

    public reorder = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.reorder(id), payload, params, headers)
}
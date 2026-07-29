import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/doctor-schedule`

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

    // schedule ops
    availableSlots: () => `${RESOURCE_ENDPOINT}/available-slots`,
    byDoctor: (doctorId: any) => `${RESOURCE_ENDPOINT}/by-doctor/${doctorId}`,
    activeSchedules: () => `${RESOURCE_ENDPOINT}/active`,
    slots: (id: any) => `${RESOURCE_ENDPOINT}/${id}/slots`,
    addSlot: (id: any) => `${RESOURCE_ENDPOINT}/${id}/slots`,
    updateSlot: (scheduleId: any, slotId: any) =>
        `${RESOURCE_ENDPOINT}/${scheduleId}/slots/${slotId}`,
    removeSlot: (scheduleId: any, slotId: any) =>
        `${RESOURCE_ENDPOINT}/${scheduleId}/slots/${slotId}`,
    exceptions: (id: any) => `${RESOURCE_ENDPOINT}/${id}/exceptions`,
    addException: (id: any) => `${RESOURCE_ENDPOINT}/${id}/exceptions`,
    removeException: (scheduleId: any, exceptionId: any) =>
        `${RESOURCE_ENDPOINT}/${scheduleId}/exceptions/${exceptionId}`,
    clone: (id: any) => `${RESOURCE_ENDPOINT}/${id}/clone`,
    setDefault: (id: any) => `${RESOURCE_ENDPOINT}/${id}/set-default`,
}

export default class DoctorScheduleApi {
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

    // ----- schedule ops -----
    // Materializes and returns the doctor's open slots for a date.
    public availableSlots = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.availableSlots(), params, headers)

    public byDoctor = (doctorId: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.byDoctor(doctorId), params, headers)

    public activeSchedules = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.activeSchedules(), params, headers)

    public slots = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.slots(id), params, headers)

    public addSlot = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.addSlot(id), payload, params, headers)

    public updateSlot = (
        scheduleId: any,
        slotId: any,
        payload = {},
        params = {},
        headers = {}
    ): AxiosPromise<any> =>
        HttpService.put(endpoints.updateSlot(scheduleId, slotId), payload, params, headers)

    public removeSlot = (scheduleId: any, slotId: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.delete(endpoints.removeSlot(scheduleId, slotId), params, headers)

    public exceptions = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.exceptions(id), params, headers)

    public addException = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.addException(id), payload, params, headers)

    public removeException = (
        scheduleId: any,
        exceptionId: any,
        params = {},
        headers = {}
    ): AxiosPromise<any> =>
        HttpService.delete(endpoints.removeException(scheduleId, exceptionId), params, headers)

    public clone = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.clone(id), payload, params, headers)

    public setDefault = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.setDefault(id), {}, params, headers)
}
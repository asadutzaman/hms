import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/prescription-dispense`

const endpoints = {
    list: () => `${RESOURCE_ENDPOINT}`,
    getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
    forPrescription: (prescriptionId: any) => `${RESOURCE_ENDPOINT}/for-prescription/${prescriptionId}`,
    shortfall: () => `${RESOURCE_ENDPOINT}/shortfall`,
    dispense: (prescriptionId: any) => `${RESOURCE_ENDPOINT}/${prescriptionId}`,
}

export default class PrescriptionDispenseApi {
    public list = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.list(), params, headers)

    public getById = (id: any): AxiosPromise<any> =>
        HttpService.get(endpoints.getById(id))

    public forPrescription = (prescriptionId: any): AxiosPromise<any> =>
        HttpService.get(endpoints.forPrescription(prescriptionId))

    public shortfall = (payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.shortfall(), payload)

    public dispense = (prescriptionId: any, payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.dispense(prescriptionId), payload)
}

import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/doctor-portal`

const endpoints = {
    dashboard: () => `${RESOURCE_ENDPOINT}/dashboard`,
    appointments: () => `${RESOURCE_ENDPOINT}/appointments`,
    patientHistory: (patientId: any) => `${RESOURCE_ENDPOINT}/patient-history/${patientId}`,
    latestPrescription: (patientId: any) => `${RESOURCE_ENDPOINT}/latest-prescription/${patientId}`,
    recentDrugs: () => `${RESOURCE_ENDPOINT}/recent-drugs`,
}

export default class DoctorPortalApi {
    public dashboard = (): AxiosPromise<any> =>
        HttpService.get(endpoints.dashboard())

    public appointments = (params = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.appointments(), params)

    public patientHistory = (patientId: any): AxiosPromise<any> =>
        HttpService.get(endpoints.patientHistory(patientId))

    public latestPrescription = (patientId: any, params = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.latestPrescription(patientId), params)

    public recentDrugs = (): AxiosPromise<any> =>
        HttpService.get(endpoints.recentDrugs())
}

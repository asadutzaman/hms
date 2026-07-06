import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/patient-history`
const endpoints = {
  timeline: (patientId: any) => `${RESOURCE_ENDPOINT}/${patientId}`,
}

export default class PatientHistoryApi {
  public timeline = (patientId: any, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.get(endpoints.timeline(patientId), params, headers)
}

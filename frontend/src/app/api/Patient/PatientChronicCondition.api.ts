import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/patient-chronic-condition`
const endpoints = {
  byPatient: (patientId: any) => `${RESOURCE_ENDPOINT}/by-patient/${patientId}`,
  getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  create: () => `${RESOURCE_ENDPOINT}`,
  addReading: (id: any) => `${RESOURCE_ENDPOINT}/${id}/reading`,
}

export default class PatientChronicConditionApi {
  public byPatient = (patientId: any, params = {}, headers = {}): AxiosPromise<any> => {
    return HttpService.get(endpoints.byPatient(patientId), params, headers)
  }

  public getById = (id: any): AxiosPromise<any> => {
    return HttpService.get(endpoints.getById(id))
  }

  public create = (payload = {}, params = {}, headers = {}): AxiosPromise<any> => {
    return HttpService.post(endpoints.create(), payload, params, headers)
  }

  public addReading = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> => {
    return HttpService.post(endpoints.addReading(id), payload, params, headers)
  }
}

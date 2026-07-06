import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/ipd-medication-administration`
const endpoints = {
  list: () => `${RESOURCE_ENDPOINT}`,
  getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  byAdmission: (admissionId: any) => `${RESOURCE_ENDPOINT}/by-admission/${admissionId}`,
  record: (id: any) => `${RESOURCE_ENDPOINT}/${id}/record`,
}

export default class IpdMedicationAdministrationApi {
  public list = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(endpoints.list(), params, headers)
  public getById = (id: any): AxiosPromise<any> => HttpService.get(endpoints.getById(id))
  public byAdmission = (admissionId: any, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.get(endpoints.byAdmission(admissionId), params, headers)
  public record = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(endpoints.record(id), payload, params, headers)
}

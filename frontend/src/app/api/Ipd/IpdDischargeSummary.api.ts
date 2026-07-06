import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/ipd-discharge-summary`
const endpoints = {
  list: () => `${RESOURCE_ENDPOINT}`,
  getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  update: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  byAdmission: (admissionId: any) => `${RESOURCE_ENDPOINT}/by-admission/${admissionId}`,
  generate: (admissionId: any) => `${RESOURCE_ENDPOINT}/generate/${admissionId}`,
  sign: (id: any) => `${RESOURCE_ENDPOINT}/${id}/sign`,
}

export default class IpdDischargeSummaryApi {
  public list = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(endpoints.list(), params, headers)
  public getById = (id: any): AxiosPromise<any> => HttpService.get(endpoints.getById(id))
  public update = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.put(endpoints.update(id), payload, params, headers)
  public byAdmission = (admissionId: any, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.get(endpoints.byAdmission(admissionId), params, headers)
  public generate = (admissionId: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(endpoints.generate(admissionId), payload, params, headers)
  public sign = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(endpoints.sign(id), {}, params, headers)
}

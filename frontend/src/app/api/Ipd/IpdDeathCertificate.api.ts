import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/ipd-death-certificate`
const endpoints = {
  list: () => `${RESOURCE_ENDPOINT}`,
  getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  create: () => `${RESOURCE_ENDPOINT}`,
  update: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  byAdmission: (admissionId: any) => `${RESOURCE_ENDPOINT}/by-admission/${admissionId}`,
  certify: (id: any) => `${RESOURCE_ENDPOINT}/${id}/certify`,
}

export default class IpdDeathCertificateApi {
  public list = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(endpoints.list(), params, headers)
  public getById = (id: any): AxiosPromise<any> => HttpService.get(endpoints.getById(id))
  public create = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(endpoints.create(), payload, params, headers)
  public update = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.put(endpoints.update(id), payload, params, headers)
  public byAdmission = (admissionId: any, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.get(endpoints.byAdmission(admissionId), params, headers)
  public certify = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(endpoints.certify(id), {}, params, headers)
}

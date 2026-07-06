import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/er-visit`
const endpoints = {
  list: () => `${RESOURCE_ENDPOINT}`,
  getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  create: () => `${RESOURCE_ENDPOINT}`,
  update: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  updatePartial: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  delete: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  bulk: () => `${RESOURCE_ENDPOINT}/bulk`,
  dropdown: () => `${RESOURCE_ENDPOINT}/dropdown`,
  board: () => `${RESOURCE_ENDPOINT}/board`,
  startTreatment: (id: any) => `${RESOURCE_ENDPOINT}/${id}/start-treatment`,
  dispose: (id: any) => `${RESOURCE_ENDPOINT}/${id}/dispose`,
  linkAdmission: (id: any) => `${RESOURCE_ENDPOINT}/${id}/link-admission`,
}

export default class ErVisitApi {
  public list = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(endpoints.list(), params, headers)
  public getById = (id: any): AxiosPromise<any> => HttpService.get(endpoints.getById(id))
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
  public dropdown = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(endpoints.dropdown(), params, headers)
  public board = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(endpoints.board(), params, headers)
  public startTreatment = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(endpoints.startTreatment(id), {}, params, headers)
  public dispose = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(endpoints.dispose(id), payload, params, headers)
  public linkAdmission = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(endpoints.linkAdmission(id), payload, params, headers)
}

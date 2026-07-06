import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/er-triage`
const endpoints = {
  list: () => `${RESOURCE_ENDPOINT}`,
  getById: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
  create: () => `${RESOURCE_ENDPOINT}`,
  byVisit: (erVisitId: any) => `${RESOURCE_ENDPOINT}/by-visit/${erVisitId}`,
}

export default class ErTriageApi {
  public list = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(endpoints.list(), params, headers)
  public getById = (id: any): AxiosPromise<any> => HttpService.get(endpoints.getById(id))
  public create = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(endpoints.create(), payload, params, headers)
  public byVisit = (erVisitId: any, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.get(endpoints.byVisit(erVisitId), params, headers)
}

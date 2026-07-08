import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/leave-type`

export default class LeaveTypeApi {
  public list = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(RESOURCE_ENDPOINT, params, headers)
  public dropdown = (params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.get(`${RESOURCE_ENDPOINT}/dropdown`, params, headers)
  public getById = (id: any): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/${id}`)
  public create = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(RESOURCE_ENDPOINT, payload, params, headers)
  public update = (id: any, payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.put(`${RESOURCE_ENDPOINT}/${id}`, payload, params, headers)
  public delete = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.delete(`${RESOURCE_ENDPOINT}/${id}`, params, headers)
}

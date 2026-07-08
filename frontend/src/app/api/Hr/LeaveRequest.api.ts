import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/leave-request`

export default class LeaveRequestApi {
  public list = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(RESOURCE_ENDPOINT, params, headers)
  public forEmployee = (employeeId: any): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/employee/${employeeId}`)
  public getById = (id: any): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/${id}`)
  public apply = (payload = {}): AxiosPromise<any> => HttpService.post(RESOURCE_ENDPOINT, payload)
}

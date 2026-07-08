import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/leave-balance`

export default class LeaveBalanceApi {
  public forEmployee = (employeeId: any, params = {}): AxiosPromise<any> =>
    HttpService.get(`${RESOURCE_ENDPOINT}/employee/${employeeId}`, params)
  public allocate = (payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/allocate`, payload)
}

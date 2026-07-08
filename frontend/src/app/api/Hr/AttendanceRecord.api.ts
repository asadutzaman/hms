import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/attendance-record`

export default class AttendanceRecordApi {
  public forEmployee = (employeeId: any, params = {}): AxiosPromise<any> =>
    HttpService.get(`${RESOURCE_ENDPOINT}/employee/${employeeId}`, params)
  public checkIn = (payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/check-in`, payload)
  public checkOut = (payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/check-out`, payload)
  public manualEntry = (payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/manual-entry`, payload)
  public sync = (payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/sync`, payload)
}

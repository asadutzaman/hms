import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/ot-booking`

export default class OtBookingApi {
  public list = (params = {}, headers = {}): AxiosPromise<any> => HttpService.get(RESOURCE_ENDPOINT, params, headers)
  public theatreSchedule = (params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.get(`${RESOURCE_ENDPOINT}/theatre-schedule`, params, headers)
  public getById = (id: any): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/${id}`)
  public create = (payload = {}, params = {}, headers = {}): AxiosPromise<any> =>
    HttpService.post(RESOURCE_ENDPOINT, payload, params, headers)
  public reschedule = (id: any, payload = {}): AxiosPromise<any> =>
    HttpService.patch(`${RESOURCE_ENDPOINT}/${id}/reschedule`, payload)
  public cancel = (id: any, payload = {}): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/${id}/cancel`, payload)
  public start = (id: any): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/${id}/start`)
  public complete = (id: any): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/${id}/complete`)
}

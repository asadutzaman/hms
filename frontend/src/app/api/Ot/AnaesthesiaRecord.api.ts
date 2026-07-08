import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/anaesthesia-record`

export default class AnaesthesiaRecordApi {
  public getByBooking = (otBookingId: any): AxiosPromise<any> =>
    HttpService.get(`${RESOURCE_ENDPOINT}/booking/${otBookingId}`)
  public create = (otBookingId: any, payload = {}): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/booking/${otBookingId}`, payload)
  public addEntry = (id: any, payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/${id}/entry`, payload)
  public end = (id: any, payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/${id}/end`, payload)
}

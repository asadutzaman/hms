import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/surgery-note`

export default class SurgeryNoteApi {
  public getByBooking = (otBookingId: any): AxiosPromise<any> =>
    HttpService.get(`${RESOURCE_ENDPOINT}/booking/${otBookingId}`)
  public savePreOp = (otBookingId: any, payload = {}): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/booking/${otBookingId}/pre-op`, payload)
  public signIn = (otBookingId: any, payload = {}): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/booking/${otBookingId}/who-sign-in`, payload)
  public timeOut = (otBookingId: any, payload = {}): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/booking/${otBookingId}/who-time-out`, payload)
  public signOut = (otBookingId: any, payload = {}): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/booking/${otBookingId}/who-sign-out`, payload)
  public saveOpNotes = (otBookingId: any, payload = {}): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/booking/${otBookingId}/op-notes`, payload)
  public surgeonSign = (otBookingId: any): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/booking/${otBookingId}/surgeon-sign`)
}

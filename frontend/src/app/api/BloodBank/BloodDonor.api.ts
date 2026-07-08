import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/blood-donor`

export default class BloodDonorApi {
  public list = (params = {}): AxiosPromise<any> => HttpService.get(RESOURCE_ENDPOINT, params)
  public eligible = (): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/eligible`)
  public getById = (id: any): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/${id}`)
  public create = (payload = {}): AxiosPromise<any> => HttpService.post(RESOURCE_ENDPOINT, payload)
  public setDeferral = (id: any, payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/${id}/defer`, payload)
}

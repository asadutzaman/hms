import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/blood-unit`

export default class BloodUnitApi {
  public list = (params = {}): AxiosPromise<any> => HttpService.get(RESOURCE_ENDPOINT, params)
  public inventory = (params = {}): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/inventory`, params)
  public inventorySummary = (): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/inventory-summary`)
  public expiringSoon = (days = 7): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/expiring-soon`, {days})
  public recordScreening = (id: any, payload = {}): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/${id}/screening`, payload)
}

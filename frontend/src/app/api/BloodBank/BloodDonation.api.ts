import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/blood-donation`

export default class BloodDonationApi {
  public list = (params = {}): AxiosPromise<any> => HttpService.get(RESOURCE_ENDPOINT, params)
  public create = (payload = {}): AxiosPromise<any> => HttpService.post(RESOURCE_ENDPOINT, payload)
}

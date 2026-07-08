import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../constants'
import {HttpService} from '../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/payment-transaction`

export default class PaymentTransactionApi {
  public list = (params = {}): AxiosPromise<any> => HttpService.get(RESOURCE_ENDPOINT, params)
}

import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../constants'
import {HttpService} from '../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/hospital-dashboard`
const endpoints = {
  getSummary: () => `${RESOURCE_ENDPOINT}/summary`,
}

export default class HospitalDashboardApi {
  public getSummary = (
    params: {start_date?: string; end_date?: string} = {},
    headers = {}
  ): AxiosPromise<any> => {
    return HttpService.get(endpoints.getSummary(), params, headers)
  }
}

import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/lab-qc`

export default class LabQcApi {
  public lots = (params = {}): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/lots`, params)
  public createLot = (payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/lots`, payload)
  public leveyJennings = (lotId: any): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/lots/${lotId}/levey-jennings`)
  public recordRun = (lotId: any, payload = {}): AxiosPromise<any> =>
    HttpService.post(`${RESOURCE_ENDPOINT}/lots/${lotId}/runs`, payload)
}

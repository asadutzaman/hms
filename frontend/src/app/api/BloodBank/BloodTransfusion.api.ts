import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/blood-transfusion`

export default class BloodTransfusionApi {
  public byPatient = (patientId: any): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/by-patient/${patientId}`)
  public create = (payload = {}): AxiosPromise<any> => HttpService.post(RESOURCE_ENDPOINT, payload)
  public complete = (id: any, payload = {}): AxiosPromise<any> => HttpService.post(`${RESOURCE_ENDPOINT}/${id}/complete`, payload)
}

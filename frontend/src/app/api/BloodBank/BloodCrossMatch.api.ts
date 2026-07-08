import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/blood-cross-match`

export default class BloodCrossMatchApi {
  public byPatient = (patientId: any): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/by-patient/${patientId}`)
  public create = (payload = {}): AxiosPromise<any> => HttpService.post(RESOURCE_ENDPOINT, payload)
}

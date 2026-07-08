import {AxiosPromise} from 'axios'
import {SERVER_PREFIX} from '../../constants/config.constant'
import {PatientHttpService} from '../../services/patientHttp.services'

const RESOURCE_ENDPOINT = `${SERVER_PREFIX}/patient-portal/payments`

export default class PatientPortalPaymentApi {
  public list = (): AxiosPromise<any> => PatientHttpService.get(RESOURCE_ENDPOINT)
  public initiate = (payload: {payable_type: string; payable_id: number; amount?: number}): AxiosPromise<any> =>
    PatientHttpService.post(`${RESOURCE_ENDPOINT}/initiate`, payload)
  public confirm = (transactionRef: string, outcome: 'success' | 'failure', failureReason?: string): AxiosPromise<any> =>
    PatientHttpService.post(`${RESOURCE_ENDPOINT}/${transactionRef}/confirm`, {outcome, failure_reason: failureReason})
}

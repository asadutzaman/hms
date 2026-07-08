import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/insurance-claim-settlement`

export default class InsuranceClaimSettlementApi {
  public byClaim = (claimId: any): AxiosPromise<any> => HttpService.get(`${RESOURCE_ENDPOINT}/by-claim/${claimId}`)
  public create = (payload = {}): AxiosPromise<any> => HttpService.post(RESOURCE_ENDPOINT, payload)
}

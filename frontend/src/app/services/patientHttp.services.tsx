import axios, {AxiosPromise, AxiosRequestConfig, AxiosResponse} from 'axios'
import PatientStorageService from './patientStorage.service'
import {API_SERVER_URL} from '../constants/config.constant'

const StorageService = new PatientStorageService()

/**
 * A fully separate axios instance/header state from the staff HttpService —
 * a patient portal tab must never clobber (or be clobbered by) a staff
 * admin tab's Authorization header if both are open in the same browser
 * (see project_hms_sprint8_scope memory).
 */
class _PatientHttpService {
  private httpClient
  private baseURL = API_SERVER_URL
  private timeout = 60000

  private headers: Record<string, string> = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    Authorization: '',
  }

  private static instance: _PatientHttpService

  static getInstance() {
    if (!_PatientHttpService.instance) {
      _PatientHttpService.instance = new _PatientHttpService()
    }
    return _PatientHttpService.instance
  }

  constructor() {
    this.httpClient = axios.create()
    this.httpClient.interceptors.response.use(
      (response) => this.handleResponse(response),
      (error) => this.handleError(error)
    )
    this.setAccessToken(StorageService.getAccessToken())
  }

  private request(config: AxiosRequestConfig & {url: string}): AxiosPromise<any> {
    return this.httpClient.request({
      ...config,
      url: this.baseURL + config.url,
      headers: {...this.headers, ...config.headers},
      timeout: this.timeout,
    })
  }

  public setAccessToken = (token: string | null) => {
    this.headers.Authorization = token ? `Bearer ${token}` : ''
  }

  public clearAccessToken = () => {
    this.headers.Authorization = ''
  }

  public get(url: string, params = {}, headers = {}, responseType?: any): AxiosPromise<any> {
    return this.request({method: 'GET', url, params, headers, responseType: responseType || 'json'})
  }

  public post(url: string, data = {}, params = {}, headers = {}): AxiosPromise<any> {
    return this.request({method: 'POST', url, data, params, headers})
  }

  public patch(url: string, data = {}, params = {}, headers = {}): AxiosPromise<any> {
    return this.request({method: 'PATCH', url, data, params, headers})
  }

  private handleResponse(response: AxiosResponse) {
    return response
  }

  private handleError(error: any) {
    if (error?.response?.status === 401) {
      StorageService.removeAccessToken()
      this.clearAccessToken()
    }
    throw error.response
  }
}

export const PatientHttpService = _PatientHttpService.getInstance()
export {StorageService as PatientStorageServiceInstance}

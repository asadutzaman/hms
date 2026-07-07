import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/backup`

const endpoints = {
    list: () => `${RESOURCE_ENDPOINT}`,
    run: () => `${RESOURCE_ENDPOINT}/run`,
    download: (id: any) => `${RESOURCE_ENDPOINT}/${id}/download`,
}

export default class BackupApi {
    public list = (params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.list(), params, headers)

    public run = (payload = {}): AxiosPromise<any> =>
        HttpService.post(endpoints.run(), payload)

    public download = (id: any): AxiosPromise<any> =>
        HttpService.get(endpoints.download(id), {}, {}, 'blob')
}

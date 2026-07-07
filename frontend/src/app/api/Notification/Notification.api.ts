import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/notification`

const endpoints = {
    my: () => `${RESOURCE_ENDPOINT}/my`,
    unreadCount: () => `${RESOURCE_ENDPOINT}/unread-count`,
    markRead: (id: any) => `${RESOURCE_ENDPOINT}/${id}/read`,
    markAllRead: () => `${RESOURCE_ENDPOINT}/read-all`,
}

export default class NotificationApi {
    public my = (params = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.my(), params)

    public unreadCount = (): AxiosPromise<any> =>
        HttpService.get(endpoints.unreadCount())

    public markRead = (id: any, payload = {}): AxiosPromise<any> =>
        HttpService.patch(endpoints.markRead(id), payload)

    public markAllRead = (payload = {}): AxiosPromise<any> =>
        HttpService.patch(endpoints.markAllRead(), payload)
}

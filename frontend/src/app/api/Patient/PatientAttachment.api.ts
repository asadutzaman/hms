import { AxiosPromise } from 'axios'
import { CONSTANT_CONFIG } from '../../constants'
import { HttpService } from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/patient-attachment`

const endpoints = {
    byPatient: (patientId: any) => `${RESOURCE_ENDPOINT}/by-patient/${patientId}`,
    upload: () => `${RESOURCE_ENDPOINT}/upload`,
    delete: (id: any) => `${RESOURCE_ENDPOINT}/${id}`,
}

export default class PatientAttachmentApi {
    public byPatient = (patientId: any, params = {}): AxiosPromise<any> =>
        HttpService.get(endpoints.byPatient(patientId), params)

    public upload = (
        file: File,
        fields: { patient_id: any; category?: string; title?: string; description?: string }
    ): AxiosPromise<any> => {
        const formData = new FormData()
        formData.append('file', file)
        formData.append('patient_id', String(fields.patient_id))
        if (fields.category) formData.append('category', fields.category)
        if (fields.title) formData.append('title', fields.title)
        if (fields.description) formData.append('description', fields.description)

        return HttpService.uploadWithFields(endpoints.upload(), formData)
    }

    public delete = (id: any, params = {}, headers = {}): AxiosPromise<any> =>
        HttpService.delete(endpoints.delete(id), params, headers)
}

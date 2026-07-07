import {AxiosPromise} from 'axios'
import {SERVER_PREFIX} from '../../constants/config.constant'
import {PatientHttpService} from '../../services/patientHttp.services'

const RESOURCE_ENDPOINT = `${SERVER_PREFIX}/patient-portal`

const endpoints = {
  requestOtp: () => `${RESOURCE_ENDPOINT}/auth/request-otp`,
  verifyOtp: () => `${RESOURCE_ENDPOINT}/auth/verify-otp`,
  logout: () => `${RESOURCE_ENDPOINT}/auth/logout`,
  me: () => `${RESOURCE_ENDPOINT}/auth/me`,
  updateProfile: () => `${RESOURCE_ENDPOINT}/auth/profile`,
  prescriptions: () => `${RESOURCE_ENDPOINT}/prescriptions`,
  prescriptionPdf: (id: number | string) => `${RESOURCE_ENDPOINT}/prescriptions/${id}/pdf`,
  labReports: () => `${RESOURCE_ENDPOINT}/lab-reports`,
  labReportPdf: (id: number | string) => `${RESOURCE_ENDPOINT}/lab-reports/${id}/pdf`,
  opdBills: () => `${RESOURCE_ENDPOINT}/bills/opd`,
  ipdBills: () => `${RESOURCE_ENDPOINT}/bills/ipd`,
  opdBillPdf: (id: number | string) => `${RESOURCE_ENDPOINT}/bills/opd/${id}/pdf`,
  ipdBillPdf: (id: number | string) => `${RESOURCE_ENDPOINT}/bills/ipd/${id}/pdf`,
  availableSlots: () => `${RESOURCE_ENDPOINT}/appointments/available-slots`,
  appointments: () => `${RESOURCE_ENDPOINT}/appointments`,
  cancelAppointment: (id: number | string) => `${RESOURCE_ENDPOINT}/appointments/${id}/cancel`,
  timeline: () => `${RESOURCE_ENDPOINT}/timeline`,
}

export default class PatientPortalApi {
  // AUTH
  public requestOtp = (payload: {email: string}): AxiosPromise<any> =>
    PatientHttpService.post(endpoints.requestOtp(), payload)

  public verifyOtp = (payload: {email: string; code: string}): AxiosPromise<any> =>
    PatientHttpService.post(endpoints.verifyOtp(), payload)

  public logout = (): AxiosPromise<any> => PatientHttpService.post(endpoints.logout())

  public me = (): AxiosPromise<any> => PatientHttpService.get(endpoints.me())

  public updateProfile = (payload: any): AxiosPromise<any> =>
    PatientHttpService.patch(endpoints.updateProfile(), payload)

  // PRESCRIPTIONS
  public getPrescriptions = (params = {}): AxiosPromise<any> =>
    PatientHttpService.get(endpoints.prescriptions(), params)

  public downloadPrescriptionPdf = (id: number | string): AxiosPromise<any> =>
    PatientHttpService.get(endpoints.prescriptionPdf(id), {}, {}, 'blob')

  // LAB REPORTS
  public getLabReports = (params = {}): AxiosPromise<any> =>
    PatientHttpService.get(endpoints.labReports(), params)

  public downloadLabReportPdf = (id: number | string): AxiosPromise<any> =>
    PatientHttpService.get(endpoints.labReportPdf(id), {}, {}, 'blob')

  // BILLS
  public getOpdBills = (params = {}): AxiosPromise<any> => PatientHttpService.get(endpoints.opdBills(), params)

  public getIpdBills = (params = {}): AxiosPromise<any> => PatientHttpService.get(endpoints.ipdBills(), params)

  public downloadOpdBillPdf = (id: number | string): AxiosPromise<any> =>
    PatientHttpService.get(endpoints.opdBillPdf(id), {}, {}, 'blob')

  public downloadIpdBillPdf = (id: number | string): AxiosPromise<any> =>
    PatientHttpService.get(endpoints.ipdBillPdf(id), {}, {}, 'blob')

  // APPOINTMENTS
  public getAvailableSlots = (params: {doctor_id: number; date: string}): AxiosPromise<any> =>
    PatientHttpService.get(endpoints.availableSlots(), params)

  public getAppointments = (params = {}): AxiosPromise<any> =>
    PatientHttpService.get(endpoints.appointments(), params)

  public bookAppointment = (payload: any): AxiosPromise<any> =>
    PatientHttpService.post(endpoints.appointments(), payload)

  public cancelAppointment = (id: number | string, payload = {}): AxiosPromise<any> =>
    PatientHttpService.post(endpoints.cancelAppointment(id), payload)

  // TIMELINE
  public getTimeline = (params = {}): AxiosPromise<any> => PatientHttpService.get(endpoints.timeline(), params)
}

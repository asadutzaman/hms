/**
 * Separate localStorage keys from the staff StorageService — a patient
 * portal tab and a staff admin tab can be open in the same browser at once,
 * and must never share or clobber each other's token.
 */
export default class PatientStorageService {
  public getAccessToken = () => {
    return localStorage.getItem('patientAccessToken')
  }

  public setAccessToken = (token: string) => {
    localStorage.setItem('patientAccessToken', token)
  }

  public removeAccessToken = () => {
    localStorage.removeItem('patientAccessToken')
  }
}

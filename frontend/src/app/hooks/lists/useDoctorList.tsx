import {useState, useEffect} from 'react'
import {UserApi} from '../../api'

// Users holding the Doctor role — the source for doctor pickers (Doctor
// Schedule). Backed by GET /user/doctors, which returns the array directly.
export const useDoctorList = () => {
  const [doctorList, setDoctorList] = useState<any[]>([])
  const [loadingDoctorList, setLoadingDoctorList] = useState<boolean>(false)

  useEffect(() => {
    loadDoctorList()
  }, [])

  const loadDoctorList = (): Promise<any> => {
    return new Promise((resolve, reject) => {
      setLoadingDoctorList(true)
      UserApi.doctors()
        .then((res) => {
          const data = res?.data
          const list = Array.isArray(data) ? data : data?.data ?? data?.results ?? []
          setDoctorList(list)
          setLoadingDoctorList(false)
          resolve(list)
        })
        .catch((err) => {
          setLoadingDoctorList(false)
          reject(err)
        })
    })
  }

  const getDoctorById = (id: any) => doctorList.find((item: any) => item.id === Number(id))

  return {loadingDoctorList, doctorList, getDoctorById}
}

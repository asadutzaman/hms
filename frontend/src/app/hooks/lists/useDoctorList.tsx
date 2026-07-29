import {useState, useEffect} from 'react'
import {UserApi} from '../../api'

// Users holding the Doctor role — the source for doctor pickers. Backed by
// GET /user/doctors, which returns the array directly. Pass a departmentId to
// narrow the list to that department (dependent Department -> Doctor dropdown).
export const useDoctorList = (departmentId?: any) => {
  const [doctorList, setDoctorList] = useState<any[]>([])
  const [loadingDoctorList, setLoadingDoctorList] = useState<boolean>(false)

  useEffect(() => {
    loadDoctorList()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [departmentId])

  const loadDoctorList = (): Promise<any> => {
    return new Promise((resolve, reject) => {
      setLoadingDoctorList(true)
      const params = departmentId ? {department_id: departmentId} : {}
      UserApi.doctors(params)
        .then((res) => {
          const data = res?.data
          const list = Array.isArray(data) ? data : data?.data ?? data?.results ?? []
          setDoctorList(list)
          setLoadingDoctorList(false)
          resolve(list)
        })
        .catch((err) => {
          setDoctorList([])
          setLoadingDoctorList(false)
          reject(err)
        })
    })
  }

  const getDoctorById = (id: any) => doctorList.find((item: any) => item.id === Number(id))

  return {loadingDoctorList, doctorList, getDoctorById}
}

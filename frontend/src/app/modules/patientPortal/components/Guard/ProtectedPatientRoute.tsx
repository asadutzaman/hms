import React, {FC} from 'react'
import {Navigate, Outlet} from 'react-router-dom'
import PatientStorageService from 'src/app/services/patientStorage.service'

const StorageService = new PatientStorageService()

/**
 * Lightweight guard, deliberately simpler than staff's ProtectedAdminRoute
 * (no AuthContext/idle-timer machinery) — just checks for a patient token
 * synchronously. The token's actual validity is enforced server-side by
 * patientAuthVerify middleware on every API call.
 */
const ProtectedPatientRoute: FC = () => {
  const token = StorageService.getAccessToken()

  if (!token) {
    return <Navigate to='/patient-portal/login' replace />
  }

  return <Outlet />
}

export default ProtectedPatientRoute

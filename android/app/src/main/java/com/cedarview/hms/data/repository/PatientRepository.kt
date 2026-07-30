package com.cedarview.hms.data.repository

import com.cedarview.hms.core.network.apiCall
import com.cedarview.hms.data.remote.api.PatientApi
import com.cedarview.hms.data.remote.dto.BookAppointmentRequest
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class PatientRepository @Inject constructor(
    private val api: PatientApi,
) {
    suspend fun home() = apiCall { api.home() }
    suspend fun doctors(departmentId: Int? = null, search: String? = null) =
        apiCall { api.doctors(departmentId, search) }
    suspend fun slots(doctorId: Int, date: String) = apiCall { api.slots(doctorId, date) }
    suspend fun appointments() = apiCall { api.appointments() }
    suspend fun book(request: BookAppointmentRequest) = apiCall { api.book(request) }
    suspend fun prescriptions() = apiCall { api.prescriptions() }
    suspend fun labReports() = apiCall { api.labReports() }
    suspend fun bills() = apiCall { api.bills() }
}

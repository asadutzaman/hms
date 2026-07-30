package com.cedarview.hms.data.repository

import com.cedarview.hms.core.network.apiCall
import com.cedarview.hms.data.remote.api.DoctorApi
import com.cedarview.hms.data.remote.dto.CodeBlueRequest
import com.cedarview.hms.data.remote.dto.PrescriptionSaveRequest
import com.cedarview.hms.data.remote.dto.SoapNoteRequest
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class DoctorRepository @Inject constructor(
    private val api: DoctorApi,
) {
    suspend fun dashboard() = apiCall { api.dashboard() }
    suspend fun schedule(date: String? = null) = apiCall { api.schedule(date) }
    suspend fun patient(id: Int) = apiCall { api.patient(id) }
    suspend fun patientHistory(id: Int) = apiCall { api.patientHistory(id) }
    suspend fun patientAllergies(id: Int) = apiCall { api.patientAllergies(id) }
    suspend fun latestPrescription(id: Int) = apiCall { api.latestPrescription(id) }
    suspend fun patientLabOrders(id: Int) = apiCall { api.patientLabOrders(id) }
    suspend fun recentDrugs() = apiCall { api.recentDrugs() }
    suspend fun savePrescription(visitId: Int, request: PrescriptionSaveRequest) =
        apiCall { api.savePrescription(visitId, request) }
    suspend fun resultsInbox() = apiCall { api.resultsInbox() }
    suspend fun soapNotes(patientId: Int? = null) = apiCall { api.soapNotes(patientId) }
    suspend fun createSoapNote(request: SoapNoteRequest) = apiCall { api.createSoapNote(request) }
    suspend fun codeBlue() = apiCall { api.codeBlue() }
    suspend fun raiseCodeBlue(request: CodeBlueRequest) = apiCall { api.raiseCodeBlue(request) }
}

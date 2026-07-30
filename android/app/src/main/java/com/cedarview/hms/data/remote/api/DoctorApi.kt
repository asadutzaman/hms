package com.cedarview.hms.data.remote.api

import com.cedarview.hms.core.network.MobileEnvelope
import com.cedarview.hms.data.remote.dto.AllergyDto
import com.cedarview.hms.data.remote.dto.CodeBlueDto
import com.cedarview.hms.data.remote.dto.CodeBlueRequest
import com.cedarview.hms.data.remote.dto.DoctorApptDto
import com.cedarview.hms.data.remote.dto.DoctorDashboardData
import com.cedarview.hms.data.remote.dto.LabOrderDto
import com.cedarview.hms.data.remote.dto.LatestRxData
import com.cedarview.hms.data.remote.dto.PatientHistoryData
import com.cedarview.hms.data.remote.dto.PatientProfileDto
import com.cedarview.hms.data.remote.dto.PrescriptionSaveRequest
import com.cedarview.hms.data.remote.dto.RecentDrugsData
import com.cedarview.hms.data.remote.dto.RxHeaderDto
import com.cedarview.hms.data.remote.dto.SoapNoteDto
import com.cedarview.hms.data.remote.dto.SoapNoteRequest
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path
import retrofit2.http.Query

/** Doctor app endpoints (staff bearer token + Doctor role). */
interface DoctorApi {

    @GET("doctor/dashboard")
    suspend fun dashboard(): MobileEnvelope<DoctorDashboardData>

    @GET("doctor/schedule")
    suspend fun schedule(@Query("date") date: String? = null): MobileEnvelope<List<DoctorApptDto>>

    @GET("doctor/patients/{id}")
    suspend fun patient(@Path("id") id: Int): MobileEnvelope<PatientProfileDto>

    @GET("doctor/patients/{id}/history")
    suspend fun patientHistory(@Path("id") id: Int): MobileEnvelope<PatientHistoryData>

    @GET("doctor/patients/{id}/allergies")
    suspend fun patientAllergies(@Path("id") id: Int): MobileEnvelope<List<AllergyDto>>

    @GET("doctor/patients/{id}/latest-prescription")
    suspend fun latestPrescription(@Path("id") id: Int): MobileEnvelope<LatestRxData>

    @GET("doctor/patients/{id}/lab-orders")
    suspend fun patientLabOrders(@Path("id") id: Int): MobileEnvelope<List<LabOrderDto>>

    @GET("doctor/recent-drugs")
    suspend fun recentDrugs(): MobileEnvelope<RecentDrugsData>

    @POST("doctor/visits/{visitId}/prescription")
    suspend fun savePrescription(
        @Path("visitId") visitId: Int,
        @Body body: PrescriptionSaveRequest,
    ): MobileEnvelope<RxHeaderDto>

    @GET("doctor/results-inbox")
    suspend fun resultsInbox(): MobileEnvelope<List<LabOrderDto>>

    @GET("doctor/soap-notes")
    suspend fun soapNotes(@Query("patient_id") patientId: Int? = null): MobileEnvelope<List<SoapNoteDto>>

    @POST("doctor/soap-notes")
    suspend fun createSoapNote(@Body body: SoapNoteRequest): MobileEnvelope<SoapNoteDto>

    @GET("doctor/code-blue")
    suspend fun codeBlue(): MobileEnvelope<List<CodeBlueDto>>

    @POST("doctor/code-blue")
    suspend fun raiseCodeBlue(@Body body: CodeBlueRequest): MobileEnvelope<CodeBlueDto>
}

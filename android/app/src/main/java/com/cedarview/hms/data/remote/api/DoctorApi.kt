package com.cedarview.hms.data.remote.api

import com.cedarview.hms.core.network.MobileEnvelope
import com.cedarview.hms.data.remote.dto.DoctorDashboardData
import com.cedarview.hms.data.remote.dto.SoapNoteDto
import com.cedarview.hms.data.remote.dto.SoapNoteRequest
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Query

/** Doctor app endpoints (staff bearer token + Doctor role). */
interface DoctorApi {

    @GET("doctor/dashboard")
    suspend fun dashboard(): MobileEnvelope<DoctorDashboardData>

    @GET("doctor/soap-notes")
    suspend fun soapNotes(@Query("patient_id") patientId: Int? = null): MobileEnvelope<List<SoapNoteDto>>

    @POST("doctor/soap-notes")
    suspend fun createSoapNote(@Body body: SoapNoteRequest): MobileEnvelope<SoapNoteDto>
}

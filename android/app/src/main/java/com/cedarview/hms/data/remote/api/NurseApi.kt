package com.cedarview.hms.data.remote.api

import com.cedarview.hms.core.network.MobileEnvelope
import com.cedarview.hms.data.remote.dto.BarcodeVerifyRequest
import com.cedarview.hms.data.remote.dto.BarcodeVerifyResult
import com.cedarview.hms.data.remote.dto.ClinicalJobDto
import com.cedarview.hms.data.remote.dto.CodeBlueDto
import com.cedarview.hms.data.remote.dto.CodeBlueRequest
import com.cedarview.hms.data.remote.dto.DischargeChecklistDto
import com.cedarview.hms.data.remote.dto.DischargeChecklistRequest
import com.cedarview.hms.data.remote.dto.FluidDayDto
import com.cedarview.hms.data.remote.dto.FluidRequest
import com.cedarview.hms.data.remote.dto.HandoverDto
import com.cedarview.hms.data.remote.dto.HandoverRequest
import com.cedarview.hms.data.remote.dto.MarDto
import com.cedarview.hms.data.remote.dto.MarRecordRequest
import com.cedarview.hms.data.remote.dto.NursingNoteDto
import com.cedarview.hms.data.remote.dto.NursingNoteRequest
import com.cedarview.hms.data.remote.dto.ShiftBoardDto
import com.cedarview.hms.data.remote.dto.TransferRequest
import com.cedarview.hms.data.remote.dto.VitalDto
import com.cedarview.hms.data.remote.dto.VitalRequest
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path

/** Nurse app endpoints (Nurse role; Super Admin override). */
interface NurseApi {

    @GET("nurse/shift-board")
    suspend fun shiftBoard(): MobileEnvelope<ShiftBoardDto>

    // N2 / N6 — MAR + barcode verify
    @GET("nurse/admissions/{id}/mar")
    suspend fun mar(@Path("id") admissionId: Int): MobileEnvelope<List<MarDto>>

    @POST("nurse/mar/{id}/record")
    suspend fun recordMar(@Path("id") id: Int, @Body body: MarRecordRequest): MobileEnvelope<MarDto>

    @POST("nurse/mar/verify-barcode")
    suspend fun verifyBarcode(@Body body: BarcodeVerifyRequest): MobileEnvelope<BarcodeVerifyResult>

    // N3 / N10 — vitals
    @GET("nurse/admissions/{id}/vitals")
    suspend fun vitals(@Path("id") admissionId: Int): MobileEnvelope<List<VitalDto>>

    @POST("nurse/vitals")
    suspend fun recordVitals(@Body body: VitalRequest): MobileEnvelope<VitalDto>

    // N7 — fluid balance
    @GET("nurse/admissions/{id}/fluid-balance")
    suspend fun fluidBalance(@Path("id") admissionId: Int): MobileEnvelope<List<FluidDayDto>>

    @POST("nurse/fluid-balance")
    suspend fun recordFluid(@Body body: FluidRequest): MobileEnvelope<FluidDayDto>

    // N8 — nursing note
    @GET("nurse/admissions/{id}/nursing-notes")
    suspend fun nursingNotes(@Path("id") admissionId: Int): MobileEnvelope<List<NursingNoteDto>>

    @POST("nurse/nursing-notes")
    suspend fun storeNursingNote(@Body body: NursingNoteRequest): MobileEnvelope<NursingNoteDto>

    // N11 — discharge checklist
    @GET("nurse/admissions/{id}/discharge-checklist")
    suspend fun dischargeChecklist(@Path("id") admissionId: Int): MobileEnvelope<DischargeChecklistDto?>

    @PUT("nurse/admissions/{id}/discharge-checklist")
    suspend fun upsertDischargeChecklist(
        @Path("id") admissionId: Int,
        @Body body: DischargeChecklistRequest,
    ): MobileEnvelope<DischargeChecklistDto>

    // N12 — transfer
    @POST("nurse/admissions/{id}/transfer")
    suspend fun transfer(@Path("id") admissionId: Int, @Body body: TransferRequest): MobileEnvelope<ShiftBoardDto>

    // N9 — rapid response
    @GET("nurse/rapid-response")
    suspend fun rapidResponse(): MobileEnvelope<List<CodeBlueDto>>

    @POST("nurse/rapid-response")
    suspend fun raiseRapidResponse(@Body body: CodeBlueRequest): MobileEnvelope<CodeBlueDto>

    // N5 — task timeline
    @GET("nurse/tasks")
    suspend fun tasks(): MobileEnvelope<List<ClinicalJobDto>>

    @POST("nurse/tasks/{id}/complete")
    suspend fun completeTask(@Path("id") id: Int): MobileEnvelope<ClinicalJobDto>

    // N4 — handover
    @GET("nurse/handovers")
    suspend fun handovers(): MobileEnvelope<List<HandoverDto>>

    @POST("nurse/handovers")
    suspend fun createHandover(@Body body: HandoverRequest): MobileEnvelope<HandoverDto>
}

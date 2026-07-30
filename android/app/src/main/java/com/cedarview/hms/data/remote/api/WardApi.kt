package com.cedarview.hms.data.remote.api

import com.cedarview.hms.core.network.MobileEnvelope
import com.cedarview.hms.data.remote.dto.AdmissionRowDto
import com.cedarview.hms.data.remote.dto.DailyReviewDto
import com.cedarview.hms.data.remote.dto.DailyReviewRequest
import com.cedarview.hms.data.remote.dto.DischargeSummaryDto
import com.cedarview.hms.data.remote.dto.FluidDayDto
import com.cedarview.hms.data.remote.dto.LabOrderDto
import com.cedarview.hms.data.remote.dto.MedOrderDto
import com.cedarview.hms.data.remote.dto.RadiologyOrderDto
import com.cedarview.hms.data.remote.dto.ShiftBoardDto
import com.cedarview.hms.data.remote.dto.TransferRequest
import com.cedarview.hms.data.remote.dto.VitalDto
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path

/** Ward-doctor (RMO) app endpoints (Doctor role). */
interface WardApi {

    @GET("ward/dashboard")
    suspend fun dashboard(): MobileEnvelope<ShiftBoardDto>

    @GET("ward/round-list")
    suspend fun roundList(): MobileEnvelope<List<AdmissionRowDto>>

    @GET("ward/results-inbox")
    suspend fun resultsInbox(): MobileEnvelope<List<LabOrderDto>>

    @GET("ward/admissions/{id}/vitals")
    suspend fun vitals(@Path("id") admissionId: Int): MobileEnvelope<List<VitalDto>>

    @GET("ward/admissions/{id}/drug-chart")
    suspend fun drugChart(@Path("id") admissionId: Int): MobileEnvelope<List<MedOrderDto>>

    @GET("ward/admissions/{id}/lab-orders")
    suspend fun labOrders(@Path("id") admissionId: Int): MobileEnvelope<List<LabOrderDto>>

    @GET("ward/admissions/{id}/radiology-orders")
    suspend fun radiologyOrders(@Path("id") admissionId: Int): MobileEnvelope<List<RadiologyOrderDto>>

    @GET("ward/admissions/{id}/fluid-balance")
    suspend fun fluidBalance(@Path("id") admissionId: Int): MobileEnvelope<List<FluidDayDto>>

    @GET("ward/admissions/{id}/discharge-summary")
    suspend fun dischargeSummary(@Path("id") admissionId: Int): MobileEnvelope<DischargeSummaryDto?>

    @POST("ward/admissions/{id}/discharge-summary/generate")
    suspend fun generateDischargeSummary(@Path("id") admissionId: Int): MobileEnvelope<DischargeSummaryDto>

    @POST("ward/discharge-summaries/{id}/sign")
    suspend fun signDischargeSummary(@Path("id") id: Int): MobileEnvelope<DischargeSummaryDto>

    @POST("ward/admissions/{id}/transfer")
    suspend fun transfer(
        @Path("id") admissionId: Int,
        @Body body: TransferRequest,
    ): MobileEnvelope<AdmissionRowDto>

    @GET("ward/admissions/{id}/daily-reviews")
    suspend fun dailyReviews(@Path("id") admissionId: Int): MobileEnvelope<List<DailyReviewDto>>

    @POST("ward/admissions/{id}/daily-reviews")
    suspend fun createDailyReview(
        @Path("id") admissionId: Int,
        @Body body: DailyReviewRequest,
    ): MobileEnvelope<DailyReviewDto>
}

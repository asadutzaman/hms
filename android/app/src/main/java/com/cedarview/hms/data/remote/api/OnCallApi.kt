package com.cedarview.hms.data.remote.api

import com.cedarview.hms.core.network.MobileEnvelope
import com.cedarview.hms.data.remote.dto.AtoeDto
import com.cedarview.hms.data.remote.dto.AtoeRequest
import com.cedarview.hms.data.remote.dto.BleepDto
import com.cedarview.hms.data.remote.dto.BleepRequest
import com.cedarview.hms.data.remote.dto.ClinicalJobDto
import com.cedarview.hms.data.remote.dto.ClinicalJobRequest
import com.cedarview.hms.data.remote.dto.EdVisitDto
import com.cedarview.hms.data.remote.dto.HandoverDto
import com.cedarview.hms.data.remote.dto.HandoverRequest
import com.cedarview.hms.data.remote.dto.OnCallConsoleDto
import com.cedarview.hms.data.remote.dto.OrderSetDto
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path

/** On-call / duty-doctor app endpoints (Doctor role). */
interface OnCallApi {

    @GET("oncall/console")
    suspend fun console(): MobileEnvelope<OnCallConsoleDto>

    @GET("oncall/jobs")
    suspend fun jobs(): MobileEnvelope<List<ClinicalJobDto>>

    @POST("oncall/jobs")
    suspend fun createJob(@Body body: ClinicalJobRequest): MobileEnvelope<ClinicalJobDto>

    @POST("oncall/jobs/{id}/claim")
    suspend fun claimJob(@Path("id") id: Int): MobileEnvelope<ClinicalJobDto>

    @POST("oncall/jobs/{id}/complete")
    suspend fun completeJob(@Path("id") id: Int): MobileEnvelope<ClinicalJobDto>

    @GET("oncall/bleeps")
    suspend fun bleeps(): MobileEnvelope<List<BleepDto>>

    @POST("oncall/bleeps")
    suspend fun raiseBleep(@Body body: BleepRequest): MobileEnvelope<BleepDto>

    @POST("oncall/bleeps/{id}/acknowledge")
    suspend fun acknowledgeBleep(@Path("id") id: Int): MobileEnvelope<BleepDto>

    @GET("oncall/assessments")
    suspend fun assessments(): MobileEnvelope<List<AtoeDto>>

    @POST("oncall/assessments")
    suspend fun createAssessment(@Body body: AtoeRequest): MobileEnvelope<AtoeDto>

    @GET("oncall/order-sets")
    suspend fun orderSets(): MobileEnvelope<List<OrderSetDto>>

    // DD6 — ED board
    @GET("oncall/ed-board")
    suspend fun edBoard(): MobileEnvelope<List<EdVisitDto>>

    // DD7 — end-of-shift handover
    @GET("oncall/handovers")
    suspend fun handovers(): MobileEnvelope<List<HandoverDto>>

    @POST("oncall/handovers")
    suspend fun createHandover(@Body body: HandoverRequest): MobileEnvelope<HandoverDto>
}

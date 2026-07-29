package com.cedarview.hms.data.remote.api

import com.cedarview.hms.core.network.MobileEnvelope
import com.cedarview.hms.data.remote.dto.LoginRequest
import com.cedarview.hms.data.remote.dto.MeData
import com.cedarview.hms.data.remote.dto.MessageData
import com.cedarview.hms.data.remote.dto.OtpRequest
import com.cedarview.hms.data.remote.dto.PatientAuthData
import com.cedarview.hms.data.remote.dto.PatientProfileDto
import com.cedarview.hms.data.remote.dto.TokenData
import com.cedarview.hms.data.remote.dto.VerifyOtpRequest
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST

/** /api/v1/mobile — staff (JWT) + patient (email-OTP) authentication. */
interface AuthApi {

    // ---- Staff ----
    @POST("auth/login")
    suspend fun staffLogin(@Body body: LoginRequest): MobileEnvelope<TokenData>

    @GET("auth/me")
    suspend fun me(): MobileEnvelope<MeData>

    @POST("auth/logout")
    suspend fun staffLogout(): MobileEnvelope<MessageData>

    // ---- Patient ----
    @POST("patient/auth/request-otp")
    suspend fun requestOtp(@Body body: OtpRequest): MobileEnvelope<MessageData>

    @POST("patient/auth/verify-otp")
    suspend fun verifyOtp(@Body body: VerifyOtpRequest): MobileEnvelope<PatientAuthData>

    @GET("patient/auth/me")
    suspend fun patientMe(): MobileEnvelope<PatientProfileDto>

    @POST("patient/auth/logout")
    suspend fun patientLogout(): MobileEnvelope<MessageData>
}

package com.cedarview.hms.data.remote.dto

import kotlinx.serialization.Serializable

// Property names are camelCase; the Retrofit Json is configured with a
// snake_case naming strategy, so they map to/from the API's snake_case keys.

@Serializable
data class LoginRequest(
    val username: String,
    val password: String,
    val deviceType: String = "mobile",
)

@Serializable
data class TokenData(
    val accessToken: String,
    val refreshToken: String? = null,
)

@Serializable
data class UserData(
    val id: Int? = null,
    val name: String? = null,
    val email: String? = null,
    val phone: String? = null,
)

@Serializable
data class MeData(
    val user: UserData? = null,
    val roles: List<String> = emptyList(),
    val scopes: List<String> = emptyList(),
)

@Serializable
data class OtpRequest(val email: String)

@Serializable
data class VerifyOtpRequest(val email: String, val code: String)

@Serializable
data class PatientAuthData(
    val accessToken: String? = null,
    val patient: PatientProfileDto? = null,
)

package com.cedarview.hms.data.repository

import com.cedarview.hms.core.auth.AuthMode
import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.network.ApiResult
import com.cedarview.hms.core.network.apiCall
import com.cedarview.hms.data.remote.api.AuthApi
import com.cedarview.hms.data.remote.dto.LoginRequest
import com.cedarview.hms.data.remote.dto.OtpRequest
import com.cedarview.hms.data.remote.dto.VerifyOtpRequest
import javax.inject.Inject
import javax.inject.Singleton

/**
 * Coordinates the two auth stacks and the [SessionManager]. On staff login it
 * saves the token, then loads /auth/me to enrich the session with roles + name.
 */
@Singleton
class AuthRepository @Inject constructor(
    private val api: AuthApi,
    private val session: SessionManager,
) {
    suspend fun staffLogin(username: String, password: String): ApiResult<Unit> {
        return when (val login = apiCall { api.staffLogin(LoginRequest(username, password)) }) {
            is ApiResult.Success -> {
                val token = login.data
                // Persist first so the interceptor authorises the /me call.
                session.saveStaffSession(token.accessToken, token.refreshToken, null, emptyList())
                when (val me = apiCall { api.me() }) {
                    is ApiResult.Success ->
                        session.saveStaffSession(token.accessToken, token.refreshToken, me.data.user?.name, me.data.roles)
                    is ApiResult.Error -> Unit // signed in; profile can be refreshed later
                }
                ApiResult.Success(Unit)
            }
            is ApiResult.Error -> login
        }
    }

    suspend fun requestOtp(email: String): ApiResult<Unit> =
        when (val r = apiCall { api.requestOtp(OtpRequest(email)) }) {
            is ApiResult.Success -> ApiResult.Success(Unit)
            is ApiResult.Error -> r
        }

    suspend fun verifyOtp(email: String, code: String): ApiResult<Unit> =
        when (val r = apiCall { api.verifyOtp(VerifyOtpRequest(email, code)) }) {
            is ApiResult.Success -> {
                val token = r.data.accessToken
                if (token.isNullOrBlank()) {
                    ApiResult.Error("No access token returned.")
                } else {
                    session.savePatientSession(token, r.data.patient?.displayName)
                    ApiResult.Success(Unit)
                }
            }
            is ApiResult.Error -> r
        }

    suspend fun logout() {
        val mode = session.state.value.mode
        runCatching {
            if (mode == AuthMode.PATIENT) api.patientLogout() else api.staffLogout()
        }
        session.clear()
    }
}

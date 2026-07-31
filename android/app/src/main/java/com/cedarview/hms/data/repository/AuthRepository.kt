package com.cedarview.hms.data.repository

import com.cedarview.hms.core.auth.AuthMode
import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.network.ApiResult
import com.cedarview.hms.core.network.apiCall
import com.cedarview.hms.data.remote.api.AuthApi
import com.cedarview.hms.data.remote.dto.LoginRequest
import com.cedarview.hms.data.remote.dto.OtpRequest
import com.cedarview.hms.data.remote.dto.VerifyOtpRequest
import kotlinx.coroutines.NonCancellable
import kotlinx.coroutines.withContext
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
                // NonCancellable: this runs in LoginViewModel's scope, which is cleared
                // as soon as saving the token flips the session to authenticated and
                // navigation leaves the login screen. Without it the /me call is
                // cancelled mid-flight ("SocketException: Socket closed") and the
                // session is left with no name and no roles — every role app then
                // shows "No access" on the launcher.
                withContext(NonCancellable) {
                    // Persist first so the interceptor authorises the /me call.
                    session.saveStaffSession(token.accessToken, token.refreshToken, null, emptyList())
                    loadStaffProfile()
                }
                ApiResult.Success(Unit)
            }
            is ApiResult.Error -> login
        }
    }

    /**
     * Re-reads /auth/me into the session. Called on app start so a cold start with
     * a persisted token has current roles, and as a retry when login-time
     * enrichment failed. No-op unless a staff session is active.
     */
    suspend fun refreshStaffProfile() {
        val current = session.state.value
        if (!current.isAuthenticated || current.mode != AuthMode.STAFF) return
        withContext(NonCancellable) { loadStaffProfile() }
    }

    /** Enriches the active session with the signed-in user's name + roles. */
    private suspend fun loadStaffProfile() {
        when (val me = apiCall { api.me() }) {
            is ApiResult.Success -> session.updateIdentity(me.data.user?.name, me.data.roles)
            is ApiResult.Error -> Unit // signed in; profile can be refreshed later
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

package com.cedarview.hms.core.auth

import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.runBlocking
import javax.inject.Inject
import javax.inject.Singleton

/** Observable session identity for the UI. */
data class SessionState(
    val isAuthenticated: Boolean = false,
    val mode: AuthMode? = null,
    val displayName: String? = null,
    val roles: List<String> = emptyList(),
) {
    fun hasRole(vararg names: String): Boolean {
        val lower = roles.map { it.lowercase() }
        return names.any { it.lowercase() in lower }
    }

    val isSuperAdmin: Boolean get() = hasRole("Super Admin")
}

/**
 * Single source of truth for the session at runtime. Holds the token in memory
 * (read synchronously by the OkHttp [AuthInterceptor]) and mirrors identity into
 * an observable [StateFlow] for navigation/UI. Hydrated once from [TokenStore]
 * on construction so a cold start with a persisted token is already signed in.
 */
@Singleton
class SessionManager @Inject constructor(
    private val tokenStore: TokenStore,
) {
    @Volatile
    var accessToken: String? = null
        private set

    private val _state = MutableStateFlow(SessionState())
    val state: StateFlow<SessionState> = _state.asStateFlow()

    init {
        val snap = runBlocking { tokenStore.snapshot() }
        applySnapshot(snap)
    }

    suspend fun saveStaffSession(accessToken: String, refreshToken: String?, displayName: String?, roles: List<String>) {
        tokenStore.save(accessToken, refreshToken, AuthMode.STAFF, displayName, roles)
        applySnapshot(tokenStore.snapshot())
    }

    suspend fun savePatientSession(accessToken: String, displayName: String?) {
        tokenStore.save(accessToken, null, AuthMode.PATIENT, displayName, emptyList())
        applySnapshot(tokenStore.snapshot())
    }

    /** Updates the name/roles of the current session without touching its tokens. */
    suspend fun updateIdentity(displayName: String?, roles: List<String>) {
        tokenStore.saveIdentity(displayName, roles)
        applySnapshot(tokenStore.snapshot())
    }

    suspend fun clear() {
        tokenStore.clear()
        accessToken = null
        _state.value = SessionState()
    }

    private fun applySnapshot(snap: AuthSnapshot) {
        accessToken = snap.accessToken
        _state.value = SessionState(
            isAuthenticated = snap.isAuthenticated,
            mode = snap.mode,
            displayName = snap.displayName,
            roles = snap.roles,
        )
    }
}

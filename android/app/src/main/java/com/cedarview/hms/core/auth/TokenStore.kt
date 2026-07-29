package com.cedarview.hms.core.auth

import android.content.Context
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.first
import javax.inject.Inject
import javax.inject.Singleton

private val Context.authDataStore by preferencesDataStore(name = "cedar_auth")

/** Which authentication stack the current session belongs to. */
enum class AuthMode { STAFF, PATIENT }

/** Immutable snapshot of the persisted auth state. */
data class AuthSnapshot(
    val accessToken: String? = null,
    val refreshToken: String? = null,
    val mode: AuthMode? = null,
    val displayName: String? = null,
    val roles: List<String> = emptyList(),
) {
    val isAuthenticated: Boolean get() = !accessToken.isNullOrBlank() && mode != null
}

/** Persists the bearer token + light session identity in encrypted-at-rest DataStore. */
@Singleton
class TokenStore @Inject constructor(
    @ApplicationContext private val context: Context,
) {
    private object Keys {
        val ACCESS = stringPreferencesKey("access_token")
        val REFRESH = stringPreferencesKey("refresh_token")
        val MODE = stringPreferencesKey("mode")
        val NAME = stringPreferencesKey("display_name")
        val ROLES = stringPreferencesKey("roles")
    }

    suspend fun snapshot(): AuthSnapshot {
        val prefs = context.authDataStore.data.first()
        return AuthSnapshot(
            accessToken = prefs[Keys.ACCESS],
            refreshToken = prefs[Keys.REFRESH],
            mode = prefs[Keys.MODE]?.let { runCatching { AuthMode.valueOf(it) }.getOrNull() },
            displayName = prefs[Keys.NAME],
            roles = prefs[Keys.ROLES]?.split("|")?.filter { it.isNotBlank() } ?: emptyList(),
        )
    }

    suspend fun save(
        accessToken: String,
        refreshToken: String? = null,
        mode: AuthMode,
        displayName: String? = null,
        roles: List<String> = emptyList(),
    ) {
        context.authDataStore.edit { p ->
            p[Keys.ACCESS] = accessToken
            if (refreshToken != null) p[Keys.REFRESH] = refreshToken
            p[Keys.MODE] = mode.name
            if (displayName != null) p[Keys.NAME] = displayName
            p[Keys.ROLES] = roles.joinToString("|")
        }
    }

    suspend fun clear() {
        context.authDataStore.edit { it.clear() }
    }
}

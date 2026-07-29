package com.cedarview.hms.core.network

import com.cedarview.hms.core.auth.SessionManager
import okhttp3.Interceptor
import okhttp3.Response
import javax.inject.Inject

/**
 * Attaches `Accept: application/json` and, when signed in, the bearer token to
 * every request. Reads the token synchronously from [SessionManager]'s in-memory
 * cache (never blocks on DataStore).
 */
class AuthInterceptor @Inject constructor(
    private val sessionManager: SessionManager,
) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val builder = chain.request().newBuilder()
            .header("Accept", "application/json")

        sessionManager.accessToken?.let { token ->
            builder.header("Authorization", "Bearer $token")
        }
        return chain.proceed(builder.build())
    }
}

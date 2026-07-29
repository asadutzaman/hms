package com.cedarview.hms.core.network

import kotlinx.serialization.json.Json
import kotlinx.serialization.json.JsonObject
import kotlinx.serialization.json.jsonPrimitive
import retrofit2.HttpException
import java.io.IOException

/**
 * Wraps a Retrofit call that returns a [MobileEnvelope], collapsing transport
 * errors, HTTP errors and the envelope's own success flag into an [ApiResult].
 * Screens/ViewModels never touch HttpException / IOException directly.
 */
suspend fun <T> apiCall(block: suspend () -> MobileEnvelope<T>): ApiResult<T> {
    return try {
        val env = block()
        val data = env.data
        when {
            env.success && data != null -> ApiResult.Success(data)
            env.success && data == null -> ApiResult.Error("Empty response from server.")
            else -> ApiResult.Error(env.message ?: "Request failed.")
        }
    } catch (e: HttpException) {
        ApiResult.Error(parseHttpError(e), e.code())
    } catch (e: IOException) {
        ApiResult.Error("Network error. Please check your connection.")
    } catch (e: Exception) {
        ApiResult.Error(e.message ?: "Something went wrong.")
    }
}

/** Variant for action endpoints whose `data` may be null (e.g. logout). */
suspend fun apiAction(block: suspend () -> MobileEnvelope<Unit>): ApiResult<Unit> {
    return try {
        val env = block()
        if (env.success) ApiResult.Success(Unit)
        else ApiResult.Error(env.message ?: "Request failed.")
    } catch (e: HttpException) {
        ApiResult.Error(parseHttpError(e), e.code())
    } catch (e: IOException) {
        ApiResult.Error("Network error. Please check your connection.")
    } catch (e: Exception) {
        ApiResult.Error(e.message ?: "Something went wrong.")
    }
}

private val errorJson = Json { ignoreUnknownKeys = true }

/** Pull the `message` out of an error envelope body, falling back to a status text. */
private fun parseHttpError(e: HttpException): String {
    return try {
        val body = e.response()?.errorBody()?.string().orEmpty()
        if (body.isNotBlank()) {
            val obj = errorJson.parseToJsonElement(body) as? JsonObject
            obj?.get("message")?.jsonPrimitive?.content ?: defaultHttpMessage(e.code())
        } else {
            defaultHttpMessage(e.code())
        }
    } catch (_: Exception) {
        defaultHttpMessage(e.code())
    }
}

private fun defaultHttpMessage(code: Int): String = when (code) {
    401 -> "Your session has expired. Please sign in again."
    403 -> "You do not have access to this area."
    404 -> "Not found."
    else -> "Request failed ($code)."
}

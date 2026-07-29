package com.cedarview.hms.core.network

import kotlinx.serialization.Serializable

/**
 * The consistent response envelope returned by every /api/v1/mobile endpoint:
 *   { "success": true, "message": "...", "data": <T>, "meta": {...} }
 * On error the backend returns { "success": false, "message": "...", "errors": ... }.
 *
 * `meta` and `errors` are intentionally left as raw types — screens read `data`.
 */
@Serializable
data class MobileEnvelope<T>(
    val success: Boolean = false,
    val message: String? = null,
    val data: T? = null,
)

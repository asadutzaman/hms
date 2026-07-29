package com.cedarview.hms.data.remote.dto

import kotlinx.serialization.Serializable

@Serializable
data class MessageData(val message: String? = null)

@Serializable
data class RefreshRequest(val refreshToken: String)

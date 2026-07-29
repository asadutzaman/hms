package com.cedarview.hms.data.remote.dto

import kotlinx.serialization.Serializable

@Serializable
data class DoctorApptDto(
    val id: Int? = null,
    val appointmentNo: String? = null,
    val startTime: String? = null,
    val status: String? = null,
    val patientName: String? = null,
    val reasonForVisit: String? = null,
)

/** GET /doctor/dashboard. `stats`/`weekTrend` are omitted (charting is future work). */
@Serializable
data class DoctorDashboardData(
    val todayAppointmentCount: Int = 0,
    val queueCount: Int = 0,
    val appointments: List<DoctorApptDto> = emptyList(),
)

/** A single SOAP note (GET/POST /doctor/soap-notes). */
@Serializable
data class SoapNoteDto(
    val id: Int? = null,
    val patientId: Int? = null,
    val subjective: String? = null,
    val objective: String? = null,
    val assessment: String? = null,
    val plan: String? = null,
    val notedAt: String? = null,
    val createdByName: String? = null,
)

@Serializable
data class SoapNoteRequest(
    val patientId: Int,
    val subjective: String? = null,
    val objective: String? = null,
    val assessment: String? = null,
    val plan: String? = null,
)

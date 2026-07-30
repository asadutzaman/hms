package com.cedarview.hms.data.remote.dto

import kotlinx.serialization.Serializable

@Serializable
data class DoctorApptDto(
    val id: Int? = null,
    val appointmentNo: String? = null,
    val patientId: Int? = null,
    val opdVisitId: Int? = null,
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

// ── D2 Patient profile ────────────────────────────────────────────────────────

/** GET /doctor/patients/{id}/history → { visits: [...] }. */
@Serializable
data class PatientHistoryData(
    val visits: List<HistoryVisitDto> = emptyList(),
)

@Serializable
data class HistoryVisitDto(
    val id: Int? = null,
    val visitNo: String? = null,
    val visitDate: String? = null,
    val chiefComplaint: String? = null,
)

/** GET /doctor/patients/{id}/latest-prescription → { prescription, items }. */
@Serializable
data class LatestRxData(
    val prescription: RxHeaderDto? = null,
    val items: List<RxItemDto> = emptyList(),
)

@Serializable
data class RxHeaderDto(
    val id: Int? = null,
    val advice: String? = null,
    val prescribedAt: String? = null,
)

@Serializable
data class RxItemDto(
    val drugId: Int? = null,
    val drugName: String? = null,
    val genericName: String? = null,
    val dose: String? = null,
    val frequency: String? = null,
    val duration: String? = null,
    val instruction: String? = null,
)

@Serializable
data class AllergyDto(
    val id: Int? = null,
    val allergen: String? = null,
    val reaction: String? = null,
    val severity: String? = null,
)

// ── D4 Prescription authoring ─────────────────────────────────────────────────

/** GET /doctor/recent-drugs → { drugs: [...] }. */
@Serializable
data class RecentDrugsData(
    val drugs: List<RecentDrugDto> = emptyList(),
)

@Serializable
data class RecentDrugDto(
    val drugId: Int? = null,
    val drugName: String? = null,
    val genericName: String? = null,
) {
    val label: String get() = drugName ?: genericName ?: "Drug"
}

/** POST /doctor/visits/{visitId}/prescription. */
@Serializable
data class PrescriptionSaveRequest(
    val advice: String? = null,
    val items: List<RxLineRequest> = emptyList(),
)

@Serializable
data class RxLineRequest(
    val drugId: Int? = null,
    val drugName: String? = null,
    val dose: String? = null,
    val frequency: String? = null,
    val duration: String? = null,
    val instruction: String? = null,
)

package com.cedarview.hms.data.remote.dto

import kotlinx.serialization.Serializable

@Serializable
data class PatientProfileDto(
    val id: Int? = null,
    val mrn: String? = null,
    val firstName: String? = null,
    val lastName: String? = null,
    val fullName: String? = null,
    val gender: String? = null,
    val bloodGroup: String? = null,
    val primaryPhone: String? = null,
    val email: String? = null,
) {
    val displayName: String
        get() = fullName?.takeIf { it.isNotBlank() }
            ?: listOfNotNull(firstName, lastName).joinToString(" ").ifBlank { "Patient" }
}

/** Exact shape of GET /patient/doctors (Find a doctor). */
@Serializable
data class DoctorDto(
    val id: Int,
    val name: String? = null,
    val departmentId: Int? = null,
    val department: String? = null,
    val designationId: Int? = null,
    val consultationFee: Double? = null,
)

@Serializable
data class MedItemDto(
    val drugName: String? = null,
    val genericName: String? = null,
    val frequency: String? = null,
    val instruction: String? = null,
    val route: String? = null,
)

@Serializable
data class NextAppointmentDto(
    val id: Int? = null,
    val appointmentNo: String? = null,
    val appointmentDate: String? = null,
    val startTime: String? = null,
    val status: String? = null,
    val reasonForVisit: String? = null,
)

@Serializable
data class BillSummaryDto(
    val id: Int? = null,
    val billNo: String? = null,
    val netAmount: Double? = null,
    val dueAmount: Double? = null,
)

/** GET /patient/home aggregation. Nested shapes tolerate unknown keys. */
@Serializable
data class HomeData(
    val nextAppointment: NextAppointmentDto? = null,
    val todaysMedications: List<MedItemDto> = emptyList(),
    val dueBills: List<BillSummaryDto> = emptyList(),
    val totalDue: Double = 0.0,
    val latestLabReport: LabReportDto? = null,
)

@Serializable
data class LabReportDto(
    val id: Int? = null,
    val orderNo: String? = null,
    val status: String? = null,
)

@Serializable
data class SlotDto(
    val id: Int? = null,
    val slotDate: String? = null,
    val startTime: String? = null,
    val endTime: String? = null,
    val available: Int? = null,
)

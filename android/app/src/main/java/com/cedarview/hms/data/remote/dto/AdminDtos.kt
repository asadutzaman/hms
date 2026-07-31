package com.cedarview.hms.data.remote.dto

import kotlinx.serialization.Serializable

@Serializable
data class PatientRefDto(
    val firstName: String? = null,
    val lastName: String? = null,
    val mrn: String? = null,
) {
    val name: String
        get() = listOfNotNull(firstName, lastName).joinToString(" ").ifBlank { "Patient" }
}

@Serializable
data class WardRefDto(val name: String? = null)

@Serializable
data class BedRefDto(val bedNumber: String? = null)

/** GET /admin/monitors/ipd — one currently-admitted inpatient. */
@Serializable
data class AdmissionRowDto(
    val id: Int? = null,
    val admissionNo: String? = null,
    val patient: PatientRefDto? = null,
    val ward: WardRefDto? = null,
    val bed: BedRefDto? = null,
)

@Serializable
data class OpdVisitRowDto(
    val id: Int? = null,
    val visitNo: String? = null,
    val tokenNumber: Int? = null,
    val status: String? = null,
    val patient: PatientRefDto? = null,
)

/** GET /admin/monitors/opd — { date, visits[], count }. */
@Serializable
data class OpdMonitorDto(
    val date: String? = null,
    val count: Int = 0,
    val visits: List<OpdVisitRowDto> = emptyList(),
)

// ── Bed board tile (BedController::board) — shared by nurse/ward/admin ─────────
@Serializable
data class BedTileDto(
    val id: Int? = null,
    val wardId: Int? = null,
    val wardName: String? = null,
    val bedNumber: String? = null,
    val bedType: String? = null,
    val bedStatus: String? = null,
    val admissionId: Int? = null,
    val admissionNo: String? = null,
    val patientName: String? = null,
    val patientAge: Int? = null,
    val patientGender: String? = null,
    val diagnosis: String? = null,
    val admissionDate: String? = null,
) {
    /** "Meera Joshi · 71 F" — the shift-board row title. */
    val nameWithDemographics: String
        get() = listOfNotNull(
            patientName?.takeIf { it.isNotBlank() } ?: "Patient",
            listOfNotNull(patientAge?.toString(), genderInitial).joinToString(" ").takeIf { it.isNotBlank() },
        ).joinToString(" · ")

    private val genderInitial: String?
        get() = patientGender?.takeIf { it.isNotBlank() && !it.equals("unknown", true) }?.first()?.uppercase()
}

// ── A1 Dashboard (AdminMobileController::dashboard) ───────────────────────────
@Serializable
data class AdminDashboardData(
    val hospital: HospitalSummaryDto? = null,
    val mis: MisDashboardDto? = null,
)

@Serializable
data class HospitalSummaryDto(
    val appointmentsToday: CountSummaryDto? = null,
    val radiologyToday: CountSummaryDto? = null,
    val bedOccupancy: OccupancyDashboardDto? = null,
    val pendingLabOrders: CountSummaryDto? = null,
    val lowStockAlerts: List<LowStockDto> = emptyList(),
)

@Serializable
data class CountSummaryDto(
    val totalCount: Int = 0,
    val pendingCount: Int = 0,
)

@Serializable
data class LowStockDto(
    val itemId: Int? = null,
    val itemInfo: LowStockItemInfoDto? = null,
)

@Serializable
data class LowStockItemInfoDto(
    val nameEn: String? = null,
    val code: String? = null,
)

@Serializable
data class MisDashboardDto(val kpis: MisKpisDto? = null)

@Serializable
data class MisKpisDto(
    val opdVisitCount: Int = 0,
    val opdRevenue: Double = 0.0,
    val ipdAdmissionCount: Int = 0,
    val ipdRevenue: Double = 0.0,
    val totalRevenue: Double = 0.0,
)

// ── A2 Bed occupancy (AdminMobileController::bedOccupancy) ─────────────────────
@Serializable
data class BedOccupancyData(
    val summary: OccupancyDashboardDto? = null,
    val board: List<BedTileDto> = emptyList(),
)

@Serializable
data class OccupancyDashboardDto(
    val summary: BedStatusSummaryDto? = null,
    val wards: List<WardOccupancyDto> = emptyList(),
)

@Serializable
data class BedStatusSummaryDto(
    val total: Int = 0,
    val vacant: Int = 0,
    val occupied: Int = 0,
    val reserved: Int = 0,
    val cleaning: Int = 0,
    val maintenance: Int = 0,
)

@Serializable
data class WardOccupancyDto(
    val wardId: Int? = null,
    val wardName: String? = null,
    val total: Int = 0,
    val vacant: Int = 0,
    val occupied: Int = 0,
)

// ── A3 Live operations (AdminMobileController::liveOps) ────────────────────────
// opd_board shape varies; we surface the ED + bed boards we can type reliably.
@Serializable
data class LiveOpsData(
    val edBoard: List<com.cedarview.hms.data.remote.dto.EdVisitDto> = emptyList(),
    val bedBoard: List<BedTileDto> = emptyList(),
)

// ── A5/A11 Collections (PaymentTransactionController::index) ───────────────────
@Serializable
data class PaymentTxnDto(
    val id: Int? = null,
    val transactionNo: String? = null,
    val amount: Double? = null,
    val paymentMethod: String? = null,
    val paymentMethodLabel: String? = null,
    val paidAt: String? = null,
    val createdAt: String? = null,
)

// ── A6 Staffing (AdminMobileController::staffing) ─────────────────────────────
@Serializable
data class StaffingData(
    val recentLeaveRequests: List<LeaveRequestDto> = emptyList(),
)

@Serializable
data class LeaveRequestDto(
    val id: Int? = null,
    val leaveType: String? = null,
    val fromDate: String? = null,
    val toDate: String? = null,
    val reason: String? = null,
    val state: String? = null,
)

// ── A7 Reports library (AdminMobileController::reports) ────────────────────────
@Serializable
data class ReportRefDto(
    val key: String? = null,
    val title: String? = null,
    val path: String? = null,
)

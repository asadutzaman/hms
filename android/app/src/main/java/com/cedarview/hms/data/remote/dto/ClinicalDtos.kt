package com.cedarview.hms.data.remote.dto

import kotlinx.serialization.Serializable

// Shared clinical DTOs used across the doctor / ward / nurse / on-call apps.
// (PatientRefDto / WardRefDto / BedRefDto live in AdminDtos.kt, same package.)

@Serializable
data class VitalDto(
    val id: Int? = null,
    val recordedAt: String? = null,
    val bpSystolic: Int? = null,
    val bpDiastolic: Int? = null,
    val bpDisplay: String? = null,
    val pulseBpm: Int? = null,
    val temperatureC: Double? = null,
    val spo2Pct: Int? = null,
    val respiratoryRate: Int? = null,
)

@Serializable
data class MedOrderDto(
    val id: Int? = null,
    val drugName: String? = null,
    val genericName: String? = null,
    val strength: String? = null,
    val doseValue: String? = null,
    val doseUnit: String? = null,
    val route: String? = null,
    val frequency: String? = null,
    val state: String? = null,
    val status: Int? = null,
)

@Serializable
data class MarDto(
    val id: Int? = null,
    val scheduledAt: String? = null,
    val administeredAt: String? = null,
    val administrationStatus: String? = null,
    val administrationStatusLabel: String? = null,
    val reason: String? = null,
    val notes: String? = null,
    // Copied through from the parent medication order (N2 needs drug + route).
    val drugName: String? = null,
    val genericName: String? = null,
    val strength: String? = null,
    val doseValue: String? = null,
    val doseUnit: String? = null,
    val route: String? = null,
    val frequency: String? = null,
    val isPrn: Boolean = false,
) {
    /** "Ceftriaxone 1 g" — what the nurse is giving. */
    val displayName: String
        get() = listOfNotNull(drugName ?: genericName, strength).joinToString(" ").ifBlank { "Scheduled dose" }

    /** "IV · 1 g · BD" — how and how much. */
    val displayRoute: String
        get() = listOfNotNull(
            route,
            listOfNotNull(doseValue, doseUnit).joinToString(" ").ifBlank { null },
            frequency,
            if (isPrn) "PRN" else null,
        ).joinToString(" · ")

    /** Anything other than a still-scheduled slot is off the nurse's due list. */
    val isSigned: Boolean
        get() = administeredAt != null || (administrationStatus != null && !administrationStatus.equals("scheduled", ignoreCase = true))

    val isGiven: Boolean
        get() = administrationStatus.equals("given", ignoreCase = true) || administeredAt != null
}

@Serializable
data class CodeBlueDto(
    val id: Int? = null,
    val eventType: String? = null,
    val location: String? = null,
    val state: String? = null,
    val severity: String? = null,
    val reason: String? = null,
    val raisedAt: String? = null,
    val patient: PatientRefDto? = null,
    val ward: WardRefDto? = null,
)

@Serializable
data class CodeBlueRequest(
    val patientId: Int? = null,
    val wardId: Int? = null,
    val location: String? = null,
    val reason: String? = null,
    val severity: String? = null,
)

@Serializable
data class ClinicalJobDto(
    val id: Int? = null,
    val title: String? = null,
    val description: String? = null,
    val jobType: String? = null,
    val priority: String? = null,
    val state: String? = null,
    val dueAt: String? = null,
    val patient: PatientRefDto? = null,
    val ward: WardRefDto? = null,
)

@Serializable
data class ClinicalJobRequest(
    val title: String,
    val description: String? = null,
    val priority: String? = "routine",
    val patientId: Int? = null,
    val wardId: Int? = null,
)

@Serializable
data class BleepDto(
    val id: Int? = null,
    val message: String? = null,
    val priority: String? = null,
    val callback: String? = null,
    val state: String? = null,
    val patient: PatientRefDto? = null,
)

@Serializable
data class BleepRequest(
    val message: String,
    val priority: String? = "routine",
    val callback: String? = null,
    val patientId: Int? = null,
)

@Serializable
data class AtoeDto(
    val id: Int? = null,
    val patientId: Int? = null,
    val airway: String? = null,
    val breathing: String? = null,
    val circulation: String? = null,
    val disability: String? = null,
    val exposure: String? = null,
    val news2Score: Int? = null,
    val impression: String? = null,
    val plan: String? = null,
    val assessedAt: String? = null,
)

@Serializable
data class AtoeRequest(
    val patientId: Int? = null,
    val airway: String? = null,
    val breathing: String? = null,
    val circulation: String? = null,
    val disability: String? = null,
    val exposure: String? = null,
    val news2Score: Int? = null,
    val impression: String? = null,
    val plan: String? = null,
)

@Serializable
data class HandoverDto(
    val id: Int? = null,
    val roleType: String? = null,
    val wardId: Int? = null,
    val shiftLabel: String? = null,
    val summary: String? = null,
    val state: String? = null,
    val handedOverAt: String? = null,
)

@Serializable
data class HandoverRequest(
    val summary: String? = null,
    val shiftLabel: String? = null,
    val wardId: Int? = null,
)

@Serializable
data class DailyReviewDto(
    val id: Int? = null,
    val reviewDate: String? = null,
    val progressNote: String? = null,
    val assessment: String? = null,
    val plan: String? = null,
)

@Serializable
data class DailyReviewRequest(
    val progressNote: String? = null,
    val assessment: String? = null,
    val plan: String? = null,
)

@Serializable
data class OrderSetDto(
    val id: Int? = null,
    val name: String? = null,
    val category: String? = null,
    val description: String? = null,
)

@Serializable
data class OnCallConsoleDto(
    val openJobs: Int = 0,
    val unreadBleeps: Int = 0,
    val jobs: List<ClinicalJobDto> = emptyList(),
    val bleeps: List<BleepDto> = emptyList(),
)

@Serializable
data class ShiftBoardDto(
    val inpatients: Int = 0,
    val medsDueNextHour: Int = 0,
    val vitalsDue: Int = 0,
    val bedBoard: List<BedTileDto> = emptyList(),
)

// ── Lab / radiology orders (LabOrderResource / RadiologyOrderResource) ─────────
@Serializable
data class LabOrderDto(
    val id: Int? = null,
    val labOrderNo: String? = null,
    val patientId: Int? = null,
    val priority: String? = null,
    val clinicalIndication: String? = null,
    val orderStatus: Int? = null,
    val orderStatusLabel: String? = null,
    val orderedAt: String? = null,
    val patientName: String? = null,
    val mrn: String? = null,
)

@Serializable
data class RadiologyOrderDto(
    val id: Int? = null,
    val radOrderNo: String? = null,
    val patientId: Int? = null,
    val priority: String? = null,
    val clinicalIndication: String? = null,
    val orderStatus: Int? = null,
    val orderStatusLabel: String? = null,
    val orderedAt: String? = null,
    val patientName: String? = null,
)

// ── Vitals write (N3) ─────────────────────────────────────────────────────────
@Serializable
data class VitalRequest(
    val admissionId: Int,
    val bpSystolic: Int? = null,
    val bpDiastolic: Int? = null,
    val pulseBpm: Int? = null,
    val temperatureC: Double? = null,
    val spo2Pct: Int? = null,
    val respiratoryRate: Int? = null,
    val painScore: Int? = null,
    val notes: String? = null,
)

// ── Fluid balance (N7 / WD9) ──────────────────────────────────────────────────
@Serializable
data class FluidDayDto(
    val date: String? = null,
    val intake: Double = 0.0,
    val output: Double = 0.0,
    val balance: Double = 0.0,
)

@Serializable
data class FluidRequest(
    val admissionId: Int,
    val balanceType: String,   // "intake" | "output"
    val category: String,
    val amountMl: Double,
    val notes: String? = null,
)

// ── Nursing note (N8, IpdNursingAssessmentResource) ───────────────────────────
@Serializable
data class NursingNoteDto(
    val id: Int? = null,
    val generalAppearance: String? = null,
    val mobilityStatus: String? = null,
    val fallRiskLevel: String? = null,
    val painAssessment: String? = null,
    val nutritionRisk: String? = null,
    val carePlanNotes: String? = null,
    val assessedAt: String? = null,
)

@Serializable
data class NursingNoteRequest(
    val admissionId: Int,
    val generalAppearance: String? = null,
    val mobilityStatus: String? = null,
    val painAssessment: String? = null,
    val carePlanNotes: String? = null,
)

// ── MAR administer (N2) ───────────────────────────────────────────────────────
@Serializable
data class MarRecordRequest(
    val administrationStatus: String,   // IpdMedicationAdministrationStatusEnum: given | held | refused | missed
    val reason: String? = null,
    val notes: String? = null,
)

@Serializable
data class BarcodeVerifyRequest(
    val admissionId: Int,
    val scannedMrn: String,
)

@Serializable
data class BarcodeVerifyResult(
    val match: Boolean = false,
    val patient: PatientRefDto? = null,
)

// ── Patient transfer (N12 / WD9) ──────────────────────────────────────────────
@Serializable
data class TransferRequest(
    val bedId: Int,
    val reason: String? = null,
)

// ── Discharge checklist (N11, net-new) ────────────────────────────────────────
@Serializable
data class ChecklistItemDto(
    val label: String? = null,
    val done: Boolean = false,
)

@Serializable
data class DischargeChecklistDto(
    val id: Int? = null,
    val ipdAdmissionId: Int? = null,
    val items: List<ChecklistItemDto> = emptyList(),
    val state: String? = null,
    val completedAt: String? = null,
)

@Serializable
data class DischargeChecklistRequest(
    val items: List<ChecklistItemDto>,
    val state: String? = null,   // "in_progress" | "complete"
)

// ── Discharge summary (WD6/WD7, IpdDischargeSummaryResource) ───────────────────
@Serializable
data class DischargeSummaryDto(
    val id: Int? = null,
    val summaryNo: String? = null,
    val admissionDiagnosis: String? = null,
    val dischargeDiagnosis: String? = null,
    val hospitalCourse: String? = null,
    val dischargeCondition: String? = null,
    val followUpInstructions: String? = null,
    val dischargeAdvice: String? = null,
    val isFinalized: Boolean = false,
    val signedByName: String? = null,
    val signedAt: String? = null,
)

// ── ED board (DD6 / A10, ErVisitResource) ─────────────────────────────────────
@Serializable
data class EdVisitDto(
    val id: Int? = null,
    val erVisitNo: String? = null,
    val arrivalMode: String? = null,
    val chiefComplaint: String? = null,
    val arrivalAt: String? = null,
    val erStatus: Int? = null,
    val erStatusLabel: String? = null,
    val disposition: String? = null,
    val patientName: String? = null,
    val mrn: String? = null,
)

@Serializable
data class EdTriageRequest(
    val patientId: Int? = null,
    val chiefComplaint: String? = null,
    val arrivalMode: String? = null,
)

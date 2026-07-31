package com.cedarview.hms.feature.nurse

import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.common.BaseViewModel
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.network.ApiResult
import com.cedarview.hms.data.remote.dto.BarcodeVerifyRequest
import com.cedarview.hms.data.remote.dto.BarcodeVerifyResult
import com.cedarview.hms.data.remote.dto.ChecklistItemDto
import com.cedarview.hms.data.remote.dto.ClinicalJobDto
import com.cedarview.hms.data.remote.dto.CodeBlueDto
import com.cedarview.hms.data.remote.dto.CodeBlueRequest
import com.cedarview.hms.data.remote.dto.DischargeChecklistDto
import com.cedarview.hms.data.remote.dto.DischargeChecklistRequest
import com.cedarview.hms.data.remote.dto.FluidDayDto
import com.cedarview.hms.data.remote.dto.FluidRequest
import com.cedarview.hms.data.remote.dto.HandoverDto
import com.cedarview.hms.data.remote.dto.HandoverRequest
import com.cedarview.hms.data.remote.dto.MarDto
import com.cedarview.hms.data.remote.dto.MarRecordRequest
import com.cedarview.hms.data.remote.dto.NursingNoteDto
import com.cedarview.hms.data.remote.dto.NursingNoteRequest
import com.cedarview.hms.data.remote.dto.ShiftBoardDto
import com.cedarview.hms.data.remote.dto.VitalDto
import com.cedarview.hms.data.remote.dto.VitalRequest
import com.cedarview.hms.data.repository.NurseRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class NurseViewModel @Inject constructor(
    private val repository: NurseRepository,
    sessionManager: SessionManager,
) : BaseViewModel() {

    val name: String = sessionManager.state.value.displayName ?: "Nurse"
    val roles: List<String> = sessionManager.state.value.roles

    private val _board = MutableStateFlow<UiState<ShiftBoardDto>>(UiState.Loading)
    val board: StateFlow<UiState<ShiftBoardDto>> = _board.asStateFlow()

    private val _rapid = MutableStateFlow<UiState<List<CodeBlueDto>>>(UiState.Loading)
    val rapid: StateFlow<UiState<List<CodeBlueDto>>> = _rapid.asStateFlow()

    private val _tasks = MutableStateFlow<UiState<List<ClinicalJobDto>>>(UiState.Loading)
    val tasks: StateFlow<UiState<List<ClinicalJobDto>>> = _tasks.asStateFlow()

    private val _handovers = MutableStateFlow<UiState<List<HandoverDto>>>(UiState.Loading)
    val handovers: StateFlow<UiState<List<HandoverDto>>> = _handovers.asStateFlow()

    // Per-admission (N2/N3/N7/N8/N11)
    private val _mar = MutableStateFlow<UiState<List<MarDto>>>(UiState.Loading)
    val mar: StateFlow<UiState<List<MarDto>>> = _mar.asStateFlow()

    private val _vitals = MutableStateFlow<UiState<List<VitalDto>>>(UiState.Loading)
    val vitals: StateFlow<UiState<List<VitalDto>>> = _vitals.asStateFlow()

    private val _fluid = MutableStateFlow<UiState<List<FluidDayDto>>>(UiState.Loading)
    val fluid: StateFlow<UiState<List<FluidDayDto>>> = _fluid.asStateFlow()

    private val _notes = MutableStateFlow<UiState<List<NursingNoteDto>>>(UiState.Loading)
    val notes: StateFlow<UiState<List<NursingNoteDto>>> = _notes.asStateFlow()

    private val _checklist = MutableStateFlow<UiState<DischargeChecklistDto?>>(UiState.Loading)
    val checklist: StateFlow<UiState<DischargeChecklistDto?>> = _checklist.asStateFlow()

    private val _barcode = MutableStateFlow<BarcodeVerifyResult?>(null)
    val barcode: StateFlow<BarcodeVerifyResult?> = _barcode.asStateFlow()

    fun loadBoard() = loadInto(_board) { repository.shiftBoard() }
    fun loadRapid() = loadInto(_rapid) { repository.rapidResponse() }
    fun loadTasks() = loadInto(_tasks) { repository.tasks() }
    fun loadHandovers() = loadInto(_handovers) { repository.handovers() }

    fun loadAdmission(admissionId: Int) {
        loadInto(_mar) { repository.mar(admissionId) }
        loadInto(_vitals) { repository.vitals(admissionId) }
        loadInto(_fluid) { repository.fluidBalance(admissionId) }
        loadInto(_notes) { repository.nursingNotes(admissionId) }
        loadInto(_checklist) { repository.dischargeChecklist(admissionId) }
        _barcode.value = null
    }

    fun recordMar(id: Int, status: String, admissionId: Int) {
        viewModelScope.launch {
            repository.recordMar(id, MarRecordRequest(administrationStatus = status))
            loadInto(_mar) { repository.mar(admissionId) }
        }
    }

    fun recordVitals(
        admissionId: Int,
        systolic: Int?,
        diastolic: Int?,
        pulse: Int?,
        temp: Double?,
        spo2: Int?,
        rr: Int?,
        painScore: Int? = null,
    ) {
        viewModelScope.launch {
            repository.recordVitals(
                VitalRequest(admissionId, systolic, diastolic, pulse, temp, spo2, rr, painScore),
            )
            loadInto(_vitals) { repository.vitals(admissionId) }
        }
    }

    fun recordFluid(admissionId: Int, type: String, category: String, amountMl: Double) {
        viewModelScope.launch {
            repository.recordFluid(FluidRequest(admissionId, type, category, amountMl))
            loadInto(_fluid) { repository.fluidBalance(admissionId) }
        }
    }

    fun addNursingNote(admissionId: Int, appearance: String, mobility: String, pain: String, carePlan: String) {
        viewModelScope.launch {
            repository.storeNursingNote(
                NursingNoteRequest(
                    admissionId = admissionId,
                    generalAppearance = appearance.ifBlank { null },
                    mobilityStatus = mobility.ifBlank { null },
                    painAssessment = pain.ifBlank { null },
                    carePlanNotes = carePlan.ifBlank { null },
                ),
            )
            loadInto(_notes) { repository.nursingNotes(admissionId) }
        }
    }

    fun saveChecklist(admissionId: Int, items: List<ChecklistItemDto>, complete: Boolean) {
        viewModelScope.launch {
            repository.upsertDischargeChecklist(
                admissionId,
                DischargeChecklistRequest(items = items, state = if (complete) "complete" else "in_progress"),
            )
            loadInto(_checklist) { repository.dischargeChecklist(admissionId) }
        }
    }

    fun verifyBarcode(admissionId: Int, mrn: String) {
        viewModelScope.launch {
            _barcode.value = when (val r = repository.verifyBarcode(BarcodeVerifyRequest(admissionId, mrn))) {
                is ApiResult.Success -> r.data
                is ApiResult.Error -> BarcodeVerifyResult(match = false)
            }
        }
    }

    fun transfer(admissionId: Int, bedId: Int, reason: String) {
        viewModelScope.launch { repository.transfer(admissionId, bedId, reason.ifBlank { null }) }
    }

    fun completeTask(id: Int) {
        viewModelScope.launch { repository.completeTask(id); loadInto(_tasks) { repository.tasks() } }
    }

    fun raiseRapid(location: String, reason: String, wardId: Int? = null) {
        viewModelScope.launch {
            repository.raiseRapidResponse(
                CodeBlueRequest(
                    wardId = wardId,
                    location = location.ifBlank { null },
                    reason = reason.ifBlank { null },
                ),
            )
            loadInto(_rapid) { repository.rapidResponse() }
        }
    }

    fun addHandover(summary: String, shiftLabel: String) {
        viewModelScope.launch {
            repository.createHandover(HandoverRequest(summary = summary.ifBlank { null }, shiftLabel = shiftLabel.ifBlank { null }))
            loadInto(_handovers) { repository.handovers() }
        }
    }
}

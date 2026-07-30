package com.cedarview.hms.feature.doctor

import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.common.BaseViewModel
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.data.remote.dto.AllergyDto
import com.cedarview.hms.data.remote.dto.CodeBlueDto
import com.cedarview.hms.data.remote.dto.CodeBlueRequest
import com.cedarview.hms.data.remote.dto.LabOrderDto
import com.cedarview.hms.data.remote.dto.LatestRxData
import com.cedarview.hms.data.remote.dto.PatientHistoryData
import com.cedarview.hms.data.remote.dto.PatientProfileDto
import com.cedarview.hms.data.remote.dto.PrescriptionSaveRequest
import com.cedarview.hms.data.remote.dto.RecentDrugsData
import com.cedarview.hms.data.remote.dto.RxLineRequest
import com.cedarview.hms.data.remote.dto.SoapNoteDto
import com.cedarview.hms.data.remote.dto.SoapNoteRequest
import com.cedarview.hms.data.repository.DoctorRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

/** Backs the Doctor app's SOAP + Code Blue tabs (Today has its own ViewModel). */
@HiltViewModel
class DoctorAppViewModel @Inject constructor(
    private val repository: DoctorRepository,
    sessionManager: SessionManager,
) : BaseViewModel() {

    val doctorName: String = sessionManager.state.value.displayName ?: "Doctor"
    val roles: List<String> = sessionManager.state.value.roles

    private val _soap = MutableStateFlow<UiState<List<SoapNoteDto>>>(UiState.Loading)
    val soap: StateFlow<UiState<List<SoapNoteDto>>> = _soap.asStateFlow()

    private val _codeBlue = MutableStateFlow<UiState<List<CodeBlueDto>>>(UiState.Loading)
    val codeBlue: StateFlow<UiState<List<CodeBlueDto>>> = _codeBlue.asStateFlow()

    // D2 patient profile (per-patient)
    private val _profile = MutableStateFlow<UiState<PatientProfileDto>>(UiState.Loading)
    val profile: StateFlow<UiState<PatientProfileDto>> = _profile.asStateFlow()

    private val _history = MutableStateFlow<UiState<PatientHistoryData>>(UiState.Loading)
    val history: StateFlow<UiState<PatientHistoryData>> = _history.asStateFlow()

    private val _allergies = MutableStateFlow<UiState<List<AllergyDto>>>(UiState.Loading)
    val allergies: StateFlow<UiState<List<AllergyDto>>> = _allergies.asStateFlow()

    private val _latestRx = MutableStateFlow<UiState<LatestRxData>>(UiState.Loading)
    val latestRx: StateFlow<UiState<LatestRxData>> = _latestRx.asStateFlow()

    private val _patientLabs = MutableStateFlow<UiState<List<LabOrderDto>>>(UiState.Loading)
    val patientLabs: StateFlow<UiState<List<LabOrderDto>>> = _patientLabs.asStateFlow()

    // D4 recent drugs / D5 results inbox
    private val _recentDrugs = MutableStateFlow<UiState<RecentDrugsData>>(UiState.Loading)
    val recentDrugs: StateFlow<UiState<RecentDrugsData>> = _recentDrugs.asStateFlow()

    private val _resultsInbox = MutableStateFlow<UiState<List<LabOrderDto>>>(UiState.Loading)
    val resultsInbox: StateFlow<UiState<List<LabOrderDto>>> = _resultsInbox.asStateFlow()

    fun loadSoap() = loadInto(_soap) { repository.soapNotes() }
    fun loadCodeBlue() = loadInto(_codeBlue) { repository.codeBlue() }
    fun loadResultsInbox() = loadInto(_resultsInbox) { repository.resultsInbox() }
    fun loadRecentDrugs() = loadInto(_recentDrugs) { repository.recentDrugs() }

    fun loadPatient(patientId: Int) {
        loadInto(_profile) { repository.patient(patientId) }
        loadInto(_history) { repository.patientHistory(patientId) }
        loadInto(_allergies) { repository.patientAllergies(patientId) }
        loadInto(_latestRx) { repository.latestPrescription(patientId) }
        loadInto(_patientLabs) { repository.patientLabOrders(patientId) }
    }

    fun savePrescription(visitId: Int, advice: String, lines: List<RxLineRequest>, onDone: () -> Unit) {
        viewModelScope.launch {
            repository.savePrescription(
                visitId,
                PrescriptionSaveRequest(advice = advice.ifBlank { null }, items = lines),
            )
            onDone()
        }
    }

    fun createSoap(patientId: Int, subjective: String, assessment: String, plan: String) {
        viewModelScope.launch {
            repository.createSoapNote(
                SoapNoteRequest(
                    patientId = patientId,
                    subjective = subjective.ifBlank { null },
                    assessment = assessment.ifBlank { null },
                    plan = plan.ifBlank { null },
                ),
            )
            loadSoap()
        }
    }

    fun raiseCodeBlue(location: String, reason: String) {
        viewModelScope.launch {
            repository.raiseCodeBlue(CodeBlueRequest(location = location.ifBlank { null }, reason = reason.ifBlank { null }))
            loadCodeBlue()
        }
    }
}

package com.cedarview.hms.feature.oncall

import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.common.BaseViewModel
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.data.remote.dto.AtoeDto
import com.cedarview.hms.data.remote.dto.AtoeRequest
import com.cedarview.hms.data.remote.dto.BleepDto
import com.cedarview.hms.data.remote.dto.BleepRequest
import com.cedarview.hms.data.remote.dto.ClinicalJobDto
import com.cedarview.hms.data.remote.dto.ClinicalJobRequest
import com.cedarview.hms.data.remote.dto.EdVisitDto
import com.cedarview.hms.data.remote.dto.HandoverDto
import com.cedarview.hms.data.remote.dto.HandoverRequest
import com.cedarview.hms.data.remote.dto.OnCallConsoleDto
import com.cedarview.hms.data.remote.dto.OrderSetDto
import com.cedarview.hms.data.repository.OnCallRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class OnCallViewModel @Inject constructor(
    private val repository: OnCallRepository,
    sessionManager: SessionManager,
) : BaseViewModel() {

    val name: String = sessionManager.state.value.displayName ?: "Doctor"
    val roles: List<String> = sessionManager.state.value.roles

    private val _console = MutableStateFlow<UiState<OnCallConsoleDto>>(UiState.Loading)
    val console: StateFlow<UiState<OnCallConsoleDto>> = _console.asStateFlow()

    private val _jobs = MutableStateFlow<UiState<List<ClinicalJobDto>>>(UiState.Loading)
    val jobs: StateFlow<UiState<List<ClinicalJobDto>>> = _jobs.asStateFlow()

    private val _bleeps = MutableStateFlow<UiState<List<BleepDto>>>(UiState.Loading)
    val bleeps: StateFlow<UiState<List<BleepDto>>> = _bleeps.asStateFlow()

    private val _assessments = MutableStateFlow<UiState<List<AtoeDto>>>(UiState.Loading)
    val assessments: StateFlow<UiState<List<AtoeDto>>> = _assessments.asStateFlow()

    private val _orderSets = MutableStateFlow<UiState<List<OrderSetDto>>>(UiState.Loading)
    val orderSets: StateFlow<UiState<List<OrderSetDto>>> = _orderSets.asStateFlow()

    private val _edBoard = MutableStateFlow<UiState<List<EdVisitDto>>>(UiState.Loading)
    val edBoard: StateFlow<UiState<List<EdVisitDto>>> = _edBoard.asStateFlow()

    private val _handovers = MutableStateFlow<UiState<List<HandoverDto>>>(UiState.Loading)
    val handovers: StateFlow<UiState<List<HandoverDto>>> = _handovers.asStateFlow()

    fun loadConsole() = loadInto(_console) { repository.console() }
    fun loadJobs() = loadInto(_jobs) { repository.jobs() }
    fun loadBleeps() = loadInto(_bleeps) { repository.bleeps() }
    fun loadAssessments() = loadInto(_assessments) { repository.assessments() }
    fun loadOrderSets() = loadInto(_orderSets) { repository.orderSets() }
    fun loadEdBoard() = loadInto(_edBoard) { repository.edBoard() }
    fun loadHandovers() = loadInto(_handovers) { repository.handovers() }

    fun addHandover(summary: String, shiftLabel: String) {
        viewModelScope.launch {
            repository.createHandover(HandoverRequest(summary = summary.ifBlank { null }, shiftLabel = shiftLabel.ifBlank { null }))
            loadInto(_handovers) { repository.handovers() }
        }
    }

    fun addJob(title: String, priority: String) {
        viewModelScope.launch {
            repository.createJob(ClinicalJobRequest(title = title, priority = priority))
            loadInto(_jobs) { repository.jobs() }
        }
    }

    fun claimJob(id: Int) = refreshingJobs { repository.claimJob(id) }
    fun completeJob(id: Int) = refreshingJobs { repository.completeJob(id) }

    private fun refreshingJobs(action: suspend () -> Any) {
        viewModelScope.launch { action(); loadInto(_jobs) { repository.jobs() } }
    }

    fun raiseBleep(message: String, priority: String) {
        viewModelScope.launch {
            repository.raiseBleep(BleepRequest(message = message, priority = priority))
            loadInto(_bleeps) { repository.bleeps() }
        }
    }

    fun acknowledgeBleep(id: Int) {
        viewModelScope.launch { repository.acknowledgeBleep(id); loadInto(_bleeps) { repository.bleeps() } }
    }

    fun addAssessment(impression: String, news2: Int?) {
        viewModelScope.launch {
            repository.createAssessment(AtoeRequest(impression = impression.ifBlank { null }, news2Score = news2))
            loadInto(_assessments) { repository.assessments() }
        }
    }
}

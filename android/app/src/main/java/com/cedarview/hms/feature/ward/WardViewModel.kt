package com.cedarview.hms.feature.ward

import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.common.BaseViewModel
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.data.remote.dto.AdmissionRowDto
import com.cedarview.hms.data.remote.dto.DailyReviewDto
import com.cedarview.hms.data.remote.dto.DailyReviewRequest
import com.cedarview.hms.data.remote.dto.DischargeSummaryDto
import com.cedarview.hms.data.remote.dto.FluidDayDto
import com.cedarview.hms.data.remote.dto.LabOrderDto
import com.cedarview.hms.data.remote.dto.MedOrderDto
import com.cedarview.hms.data.remote.dto.RadiologyOrderDto
import com.cedarview.hms.data.remote.dto.ShiftBoardDto
import com.cedarview.hms.data.remote.dto.VitalDto
import com.cedarview.hms.data.repository.WardRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class WardViewModel @Inject constructor(
    private val repository: WardRepository,
    sessionManager: SessionManager,
) : BaseViewModel() {

    val name: String = sessionManager.state.value.displayName ?: "Doctor"
    val roles: List<String> = sessionManager.state.value.roles

    private val _dashboard = MutableStateFlow<UiState<ShiftBoardDto>>(UiState.Loading)
    val dashboard: StateFlow<UiState<ShiftBoardDto>> = _dashboard.asStateFlow()

    private val _roundList = MutableStateFlow<UiState<List<AdmissionRowDto>>>(UiState.Loading)
    val roundList: StateFlow<UiState<List<AdmissionRowDto>>> = _roundList.asStateFlow()

    private val _resultsInbox = MutableStateFlow<UiState<List<LabOrderDto>>>(UiState.Loading)
    val resultsInbox: StateFlow<UiState<List<LabOrderDto>>> = _resultsInbox.asStateFlow()

    private val _vitals = MutableStateFlow<UiState<List<VitalDto>>>(UiState.Loading)
    val vitals: StateFlow<UiState<List<VitalDto>>> = _vitals.asStateFlow()

    private val _drugChart = MutableStateFlow<UiState<List<MedOrderDto>>>(UiState.Loading)
    val drugChart: StateFlow<UiState<List<MedOrderDto>>> = _drugChart.asStateFlow()

    private val _labOrders = MutableStateFlow<UiState<List<LabOrderDto>>>(UiState.Loading)
    val labOrders: StateFlow<UiState<List<LabOrderDto>>> = _labOrders.asStateFlow()

    private val _radiologyOrders = MutableStateFlow<UiState<List<RadiologyOrderDto>>>(UiState.Loading)
    val radiologyOrders: StateFlow<UiState<List<RadiologyOrderDto>>> = _radiologyOrders.asStateFlow()

    private val _fluidBalance = MutableStateFlow<UiState<List<FluidDayDto>>>(UiState.Loading)
    val fluidBalance: StateFlow<UiState<List<FluidDayDto>>> = _fluidBalance.asStateFlow()

    private val _reviews = MutableStateFlow<UiState<List<DailyReviewDto>>>(UiState.Loading)
    val reviews: StateFlow<UiState<List<DailyReviewDto>>> = _reviews.asStateFlow()

    private val _discharge = MutableStateFlow<UiState<DischargeSummaryDto?>>(UiState.Loading)
    val discharge: StateFlow<UiState<DischargeSummaryDto?>> = _discharge.asStateFlow()

    fun loadDashboard() = loadInto(_dashboard) { repository.dashboard() }
    fun loadRoundList() = loadInto(_roundList) { repository.roundList() }
    fun loadResultsInbox() = loadInto(_resultsInbox) { repository.resultsInbox() }

    fun loadAdmission(admissionId: Int) {
        loadInto(_vitals) { repository.vitals(admissionId) }
        loadInto(_drugChart) { repository.drugChart(admissionId) }
        loadInto(_labOrders) { repository.labOrders(admissionId) }
        loadInto(_radiologyOrders) { repository.radiologyOrders(admissionId) }
        loadInto(_fluidBalance) { repository.fluidBalance(admissionId) }
        loadInto(_reviews) { repository.dailyReviews(admissionId) }
        loadInto(_discharge) { repository.dischargeSummary(admissionId) }
    }

    fun addReview(admissionId: Int, progressNote: String, assessment: String, plan: String) {
        viewModelScope.launch {
            repository.createDailyReview(
                admissionId,
                DailyReviewRequest(
                    progressNote = progressNote.ifBlank { null },
                    assessment = assessment.ifBlank { null },
                    plan = plan.ifBlank { null },
                ),
            )
            loadInto(_reviews) { repository.dailyReviews(admissionId) }
        }
    }

    fun generateDischarge(admissionId: Int) {
        viewModelScope.launch {
            repository.generateDischargeSummary(admissionId)
            loadInto(_discharge) { repository.dischargeSummary(admissionId) }
        }
    }

    fun signDischarge(summaryId: Int, admissionId: Int) {
        viewModelScope.launch {
            repository.signDischargeSummary(summaryId)
            loadInto(_discharge) { repository.dischargeSummary(admissionId) }
        }
    }
}

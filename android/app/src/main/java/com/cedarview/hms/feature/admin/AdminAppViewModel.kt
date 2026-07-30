package com.cedarview.hms.feature.admin

import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.common.BaseViewModel
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.data.remote.dto.AdmissionRowDto
import com.cedarview.hms.data.remote.dto.AdminDashboardData
import com.cedarview.hms.data.remote.dto.BedOccupancyData
import com.cedarview.hms.data.remote.dto.EdVisitDto
import com.cedarview.hms.data.remote.dto.LiveOpsData
import com.cedarview.hms.data.remote.dto.OpdMonitorDto
import com.cedarview.hms.data.remote.dto.ReportRefDto
import com.cedarview.hms.data.remote.dto.StaffingData
import com.cedarview.hms.data.repository.AdminRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import javax.inject.Inject

/** Backs the Admin overview / beds / live / monitors / more tabs. */
@HiltViewModel
class AdminAppViewModel @Inject constructor(
    private val repository: AdminRepository,
    sessionManager: SessionManager,
) : BaseViewModel() {

    val name: String = sessionManager.state.value.displayName ?: "Administrator"
    val roles: List<String> = sessionManager.state.value.roles

    private val _dashboard = MutableStateFlow<UiState<AdminDashboardData>>(UiState.Loading)
    val dashboard: StateFlow<UiState<AdminDashboardData>> = _dashboard.asStateFlow()

    private val _beds = MutableStateFlow<UiState<BedOccupancyData>>(UiState.Loading)
    val beds: StateFlow<UiState<BedOccupancyData>> = _beds.asStateFlow()

    private val _liveOps = MutableStateFlow<UiState<LiveOpsData>>(UiState.Loading)
    val liveOps: StateFlow<UiState<LiveOpsData>> = _liveOps.asStateFlow()

    private val _opd = MutableStateFlow<UiState<OpdMonitorDto>>(UiState.Loading)
    val opd: StateFlow<UiState<OpdMonitorDto>> = _opd.asStateFlow()

    private val _ipd = MutableStateFlow<UiState<List<AdmissionRowDto>>>(UiState.Loading)
    val ipd: StateFlow<UiState<List<AdmissionRowDto>>> = _ipd.asStateFlow()

    private val _emergency = MutableStateFlow<UiState<List<EdVisitDto>>>(UiState.Loading)
    val emergency: StateFlow<UiState<List<EdVisitDto>>> = _emergency.asStateFlow()

    private val _staffing = MutableStateFlow<UiState<StaffingData>>(UiState.Loading)
    val staffing: StateFlow<UiState<StaffingData>> = _staffing.asStateFlow()

    private val _reports = MutableStateFlow<UiState<List<ReportRefDto>>>(UiState.Loading)
    val reports: StateFlow<UiState<List<ReportRefDto>>> = _reports.asStateFlow()

    fun loadDashboard() = loadInto(_dashboard) { repository.dashboard() }
    fun loadBeds() = loadInto(_beds) { repository.bedOccupancy() }
    fun loadLiveOps() = loadInto(_liveOps) { repository.liveOps() }
    fun loadOpd() = loadInto(_opd) { repository.opdMonitor() }
    fun loadIpd() = loadInto(_ipd) { repository.ipdMonitor() }
    fun loadEmergency() = loadInto(_emergency) { repository.emergencyMonitor() }
    fun loadStaffing() = loadInto(_staffing) { repository.staffing() }
    fun loadReports() = loadInto(_reports) { repository.reports() }
}

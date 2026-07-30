package com.cedarview.hms.feature.patient

import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.common.BaseViewModel
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.data.remote.dto.AppointmentDto
import com.cedarview.hms.data.remote.dto.BillsData
import com.cedarview.hms.data.remote.dto.LabReportDto
import com.cedarview.hms.data.remote.dto.PrescriptionDto
import com.cedarview.hms.data.repository.PatientRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import javax.inject.Inject

/** Backs the Patient app's Visits / Records / Pay tabs (one shared instance). */
@HiltViewModel
class PatientDataViewModel @Inject constructor(
    private val repository: PatientRepository,
    sessionManager: SessionManager,
) : BaseViewModel() {

    val patientName: String = sessionManager.state.value.displayName ?: "Patient"

    private val _appointments = MutableStateFlow<UiState<List<AppointmentDto>>>(UiState.Loading)
    val appointments: StateFlow<UiState<List<AppointmentDto>>> = _appointments.asStateFlow()

    private val _prescriptions = MutableStateFlow<UiState<List<PrescriptionDto>>>(UiState.Loading)
    val prescriptions: StateFlow<UiState<List<PrescriptionDto>>> = _prescriptions.asStateFlow()

    private val _labReports = MutableStateFlow<UiState<List<LabReportDto>>>(UiState.Loading)
    val labReports: StateFlow<UiState<List<LabReportDto>>> = _labReports.asStateFlow()

    private val _bills = MutableStateFlow<UiState<BillsData>>(UiState.Loading)
    val bills: StateFlow<UiState<BillsData>> = _bills.asStateFlow()

    fun loadAppointments() = loadInto(_appointments) { repository.appointments() }
    fun loadPrescriptions() = loadInto(_prescriptions) { repository.prescriptions() }
    fun loadLabReports() = loadInto(_labReports) { repository.labReports() }
    fun loadBills() = loadInto(_bills) { repository.bills() }
}

package com.cedarview.hms.feature.doctor.dashboard

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.network.ApiResult
import com.cedarview.hms.data.remote.dto.DoctorDashboardData
import com.cedarview.hms.data.repository.DoctorRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class DoctorDashboardViewModel @Inject constructor(
    private val repository: DoctorRepository,
    sessionManager: SessionManager,
) : ViewModel() {

    val doctorName: String = sessionManager.state.value.displayName ?: "Doctor"

    private val _state = MutableStateFlow<UiState<DoctorDashboardData>>(UiState.Loading)
    val state: StateFlow<UiState<DoctorDashboardData>> = _state.asStateFlow()

    init { load() }

    fun load() {
        _state.value = UiState.Loading
        viewModelScope.launch {
            _state.value = when (val r = repository.dashboard()) {
                is ApiResult.Success -> UiState.Success(r.data)
                is ApiResult.Error -> UiState.Error(r.message)
            }
        }
    }
}

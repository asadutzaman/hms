package com.cedarview.hms.feature.patient.home

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.network.ApiResult
import com.cedarview.hms.data.remote.dto.HomeData
import com.cedarview.hms.data.repository.PatientRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PatientHomeViewModel @Inject constructor(
    private val repository: PatientRepository,
    sessionManager: SessionManager,
) : ViewModel() {

    val patientName: String = sessionManager.state.value.displayName ?: "there"

    private val _state = MutableStateFlow<UiState<HomeData>>(UiState.Loading)
    val state: StateFlow<UiState<HomeData>> = _state.asStateFlow()

    init { load() }

    fun load() {
        _state.value = UiState.Loading
        viewModelScope.launch {
            _state.value = when (val r = repository.home()) {
                is ApiResult.Success -> UiState.Success(r.data)
                is ApiResult.Error -> UiState.Error(r.message)
            }
        }
    }
}

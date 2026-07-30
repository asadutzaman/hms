package com.cedarview.hms.feature.patient.doctors

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.network.ApiResult
import com.cedarview.hms.data.remote.dto.DoctorDto
import com.cedarview.hms.data.repository.PatientRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class FindDoctorViewModel @Inject constructor(
    private val repository: PatientRepository,
) : ViewModel() {

    private val _query = MutableStateFlow("")
    val query: StateFlow<String> = _query.asStateFlow()

    private val _state = MutableStateFlow<UiState<List<DoctorDto>>>(UiState.Loading)
    val state: StateFlow<UiState<List<DoctorDto>>> = _state.asStateFlow()

    init { load() }

    fun onQuery(value: String) { _query.value = value }

    fun search() = load(_query.value.trim().ifBlank { null })

    fun load(search: String? = null) {
        _state.value = UiState.Loading
        viewModelScope.launch {
            _state.value = when (val r = repository.doctors(search = search)) {
                is ApiResult.Success -> UiState.Success(r.data)
                is ApiResult.Error -> UiState.Error(r.message)
            }
        }
    }
}

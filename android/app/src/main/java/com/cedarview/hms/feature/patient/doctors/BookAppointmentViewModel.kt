package com.cedarview.hms.feature.patient.doctors

import androidx.lifecycle.SavedStateHandle
import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.common.BaseViewModel
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.network.ApiResult
import com.cedarview.hms.data.remote.dto.BookAppointmentRequest
import com.cedarview.hms.data.remote.dto.SlotDto
import com.cedarview.hms.data.repository.PatientRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Locale
import javax.inject.Inject

@HiltViewModel
class BookAppointmentViewModel @Inject constructor(
    private val repository: PatientRepository,
    savedStateHandle: SavedStateHandle,
) : BaseViewModel() {

    val doctorId: Int = savedStateHandle.get<String>("doctorId")?.toIntOrNull() ?: 0

    // Tomorrow, computed without java.time (minSdk 24, no desugaring).
    val date: String = run {
        val cal = Calendar.getInstance().apply { add(Calendar.DAY_OF_YEAR, 1) }
        SimpleDateFormat("yyyy-MM-dd", Locale.US).format(cal.time)
    }

    private val _slots = MutableStateFlow<UiState<List<SlotDto>>>(UiState.Loading)
    val slots: StateFlow<UiState<List<SlotDto>>> = _slots.asStateFlow()

    private val _booking = MutableStateFlow(false)
    val booking: StateFlow<Boolean> = _booking.asStateFlow()

    private val _result = MutableStateFlow<String?>(null)
    val result: StateFlow<String?> = _result.asStateFlow()

    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()

    init { loadSlots() }

    fun loadSlots() = loadInto(_slots) { repository.slots(doctorId, date) }

    fun book(time: String) {
        _booking.value = true
        _error.value = null
        viewModelScope.launch {
            val req = BookAppointmentRequest(doctorId = doctorId, appointmentDate = date, appointmentTime = time)
            when (val r = repository.book(req)) {
                is ApiResult.Success -> _result.value = "Appointment requested for $date at $time."
                is ApiResult.Error -> _error.value = r.message
            }
            _booking.value = false
        }
    }
}

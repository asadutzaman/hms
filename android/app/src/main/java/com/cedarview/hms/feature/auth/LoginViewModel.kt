package com.cedarview.hms.feature.auth

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.auth.AuthMode
import com.cedarview.hms.core.network.ApiResult
import com.cedarview.hms.data.repository.AuthRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

enum class LoginTab { STAFF, PATIENT }

data class LoginUiState(
    val tab: LoginTab = LoginTab.STAFF,
    val username: String = "",
    val password: String = "",
    val email: String = "",
    val otp: String = "",
    val otpSent: Boolean = false,
    val loading: Boolean = false,
    val error: String? = null,
    val info: String? = null,
    val success: AuthMode? = null,
)

@HiltViewModel
class LoginViewModel @Inject constructor(
    private val authRepository: AuthRepository,
) : ViewModel() {

    private val _state = MutableStateFlow(LoginUiState())
    val state: StateFlow<LoginUiState> = _state.asStateFlow()

    fun setTab(tab: LoginTab) = _state.update { it.copy(tab = tab, error = null, info = null) }
    fun onUsername(v: String) = _state.update { it.copy(username = v) }
    fun onPassword(v: String) = _state.update { it.copy(password = v) }
    fun onEmail(v: String) = _state.update { it.copy(email = v) }
    fun onOtp(v: String) = _state.update { it.copy(otp = v) }
    fun consumeError() = _state.update { it.copy(error = null) }

    fun staffLogin() {
        val s = _state.value
        if (s.username.isBlank() || s.password.isBlank()) {
            _state.update { it.copy(error = "Enter your Cedarview ID and password.") }
            return
        }
        _state.update { it.copy(loading = true, error = null) }
        viewModelScope.launch {
            when (val r = authRepository.staffLogin(s.username.trim(), s.password)) {
                is ApiResult.Success -> _state.update { it.copy(loading = false, success = AuthMode.STAFF) }
                is ApiResult.Error -> _state.update { it.copy(loading = false, error = r.message) }
            }
        }
    }

    fun requestOtp() {
        val s = _state.value
        if (s.email.isBlank()) {
            _state.update { it.copy(error = "Enter your registered email.") }
            return
        }
        _state.update { it.copy(loading = true, error = null) }
        viewModelScope.launch {
            when (val r = authRepository.requestOtp(s.email.trim())) {
                is ApiResult.Success -> _state.update {
                    it.copy(loading = false, otpSent = true, info = "If the email is registered, a code has been sent.")
                }
                is ApiResult.Error -> _state.update { it.copy(loading = false, error = r.message) }
            }
        }
    }

    fun verifyOtp() {
        val s = _state.value
        if (s.otp.length != 6) {
            _state.update { it.copy(error = "Enter the 6-digit code.") }
            return
        }
        _state.update { it.copy(loading = true, error = null) }
        viewModelScope.launch {
            when (val r = authRepository.verifyOtp(s.email.trim(), s.otp.trim())) {
                is ApiResult.Success -> _state.update { it.copy(loading = false, success = AuthMode.PATIENT) }
                is ApiResult.Error -> _state.update { it.copy(loading = false, error = r.message) }
            }
        }
    }
}

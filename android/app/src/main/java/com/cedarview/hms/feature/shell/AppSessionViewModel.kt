package com.cedarview.hms.feature.shell

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.auth.SessionManager
import com.cedarview.hms.core.auth.SessionState
import com.cedarview.hms.data.repository.AuthRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

/** Exposes the live session and logout, shared by the launcher and app shells. */
@HiltViewModel
class AppSessionViewModel @Inject constructor(
    sessionManager: SessionManager,
    private val authRepository: AuthRepository,
) : ViewModel() {
    val session: StateFlow<SessionState> = sessionManager.state

    fun logout() {
        viewModelScope.launch { authRepository.logout() }
    }
}

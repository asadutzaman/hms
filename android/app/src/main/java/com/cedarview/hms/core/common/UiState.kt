package com.cedarview.hms.core.common

/** Generic screen state used by every ViewModel to drive load/success/error UI. */
sealed interface UiState<out T> {
    data object Loading : UiState<Nothing>
    data class Success<T>(val data: T) : UiState<T>
    data class Error(val message: String) : UiState<Nothing>
}

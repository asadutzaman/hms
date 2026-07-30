package com.cedarview.hms.core.common

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.cedarview.hms.core.network.ApiResult
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.launch

/**
 * Base for feature ViewModels: [loadInto] runs a repository call and drives a
 * [UiState] flow through Loading → Success/Error, so screens stay declarative.
 */
abstract class BaseViewModel : ViewModel() {

    protected fun <T> loadInto(flow: MutableStateFlow<UiState<T>>, block: suspend () -> ApiResult<T>) {
        flow.value = UiState.Loading
        viewModelScope.launch {
            flow.value = when (val r = block()) {
                is ApiResult.Success -> UiState.Success(r.data)
                is ApiResult.Error -> UiState.Error(r.message)
            }
        }
    }

    /** Fire-and-forget action that refreshes [refresh] on completion. */
    protected fun action(block: suspend () -> ApiResult<*>, onDone: () -> Unit = {}) {
        viewModelScope.launch {
            block()
            onDone()
        }
    }
}

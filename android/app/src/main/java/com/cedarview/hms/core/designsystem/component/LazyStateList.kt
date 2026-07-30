package com.cedarview.hms.core.designsystem.component

import androidx.compose.foundation.lazy.LazyListScope
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.theme.CedarMuted

/**
 * Renders a [UiState] of a list inside a LazyColumn as inline cards — safe in a
 * lazy scope (never uses the full-screen state views, which require bounded
 * height). Loading/error/empty each render a single card.
 */
fun <T> LazyListScope.uiStateItems(
    state: UiState<List<T>>,
    onRetry: () -> Unit,
    emptyMessage: String,
    row: @Composable (T) -> Unit,
) {
    when (state) {
        is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
        is UiState.Error -> item {
            CedarCard {
                Text(state.message, color = CedarMuted)
                TextButton(onClick = onRetry) { Text("Retry") }
            }
        }
        is UiState.Success -> {
            if (state.data.isEmpty()) item { CedarCard { Text(emptyMessage, color = CedarMuted) } }
            else items(state.data) { row(it) }
        }
    }
}

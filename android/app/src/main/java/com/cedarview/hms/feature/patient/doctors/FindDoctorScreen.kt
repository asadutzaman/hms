package com.cedarview.hms.feature.patient.doctors

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.Avatar
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.CedarTextField
import com.cedarview.hms.core.designsystem.component.EmptyView
import com.cedarview.hms.core.designsystem.component.ErrorView
import com.cedarview.hms.core.designsystem.component.LoadingView
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.data.remote.dto.DoctorDto

@Composable
fun FindDoctorScreen(
    onBack: () -> Unit,
    onBook: (Int) -> Unit,
    viewModel: FindDoctorViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()
    val query by viewModel.query.collectAsStateWithLifecycle()

    CedarScaffold(title = "Find a doctor", onBack = onBack) { padding ->
        Column(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp)) {
            CedarTextField(
                value = query,
                onValueChange = viewModel::onQuery,
                label = "Search by name",
                modifier = Modifier.padding(vertical = 8.dp),
            )
            when (val s = state) {
                is UiState.Loading -> LoadingView()
                is UiState.Error -> ErrorView(s.message, onRetry = viewModel::search)
                is UiState.Success -> {
                    if (s.data.isEmpty()) {
                        EmptyView("No doctors match your search.")
                    } else {
                        LazyColumn(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                            items(s.data, key = { it.id }) { DoctorRow(it, onBook) }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun DoctorRow(doctor: DoctorDto, onBook: (Int) -> Unit) {
    CedarCard(Modifier.clickable { onBook(doctor.id) }) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Avatar(doctor.name)
            Column(Modifier.padding(start = 12.dp).weight(1f)) {
                Text(doctor.name ?: "Doctor", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                Text(doctor.department ?: "General", style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
            }
            Column(horizontalAlignment = Alignment.End) {
                doctor.consultationFee?.let {
                    Text("$" + "%.0f".format(it), fontWeight = FontWeight.Bold, color = CedarInk)
                }
                StatusPill("Book")
            }
        }
    }
}

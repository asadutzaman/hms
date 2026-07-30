package com.cedarview.hms.feature.doctor.dashboard

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
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.EmptyView
import com.cedarview.hms.core.designsystem.component.ErrorView
import com.cedarview.hms.core.designsystem.component.LoadingView
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.data.remote.dto.DoctorApptDto
import com.cedarview.hms.data.remote.dto.DoctorDashboardData

@Composable
fun DoctorDashboardScreen(
    onSignOut: () -> Unit,
    onOpenPatient: (DoctorApptDto) -> Unit = {},
    viewModel: DoctorDashboardViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()

    CedarScaffold(
        title = "Dr. ${viewModel.doctorName}",
        actions = { TextButton(onClick = onSignOut) { Text("Sign out") } },
    ) { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = viewModel::load)
            is UiState.Success -> DashboardContent(s.data, onOpenPatient, Modifier.padding(padding))
        }
    }
}

@Composable
private fun DashboardContent(data: DoctorDashboardData, onOpenPatient: (DoctorApptDto) -> Unit, modifier: Modifier) {
    Column(modifier.fillMaxSize().padding(horizontal = 18.dp)) {
        Row(Modifier.fillMaxWidth().padding(vertical = 8.dp), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
            StatTile("OPD today", data.todayAppointmentCount.toString(), Modifier.weight(1f))
            StatTile("In queue", data.queueCount.toString(), Modifier.weight(1f))
        }
        SectionHeader("Today's schedule")
        if (data.appointments.isEmpty()) {
            EmptyView("No appointments today.")
        } else {
            LazyColumn(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                items(data.appointments, key = { it.id ?: it.hashCode() }) { ApptRow(it, onOpenPatient) }
            }
        }
    }
}

@Composable
private fun StatTile(label: String, value: String, modifier: Modifier = Modifier) {
    CedarCard(modifier) {
        Text(value, style = MaterialTheme.typography.headlineMedium, color = CedarPrimary, fontWeight = FontWeight.Bold)
        Text(label, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
    }
}

@Composable
private fun ApptRow(appt: DoctorApptDto, onOpenPatient: (DoctorApptDto) -> Unit) {
    CedarCard(
        modifier = if (appt.patientId != null) Modifier.clickable { onOpenPatient(appt) } else Modifier,
    ) {
        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.SpaceBetween, modifier = Modifier.fillMaxWidth()) {
            Column(Modifier.weight(1f)) {
                Text(appt.patientName ?: "Patient", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                Text(
                    listOfNotNull(appt.startTime, appt.reasonForVisit).joinToString(" · ").ifBlank { "Scheduled" },
                    style = MaterialTheme.typography.bodyMedium, color = CedarMuted,
                )
            }
            StatusPill(appt.status ?: "booked", statusTone(appt.status))
        }
    }
}

private fun statusTone(status: String?): Tone = when (status?.lowercase()) {
    "completed", "done" -> Tone.Ok
    "waiting", "next" -> Tone.Warn
    "cancelled", "no_show" -> Tone.Crit
    else -> Tone.Primary
}

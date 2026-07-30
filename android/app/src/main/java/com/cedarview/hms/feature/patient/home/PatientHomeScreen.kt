package com.cedarview.hms.feature.patient.home

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
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
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.data.remote.dto.HomeData

@Composable
fun PatientHomeScreen(
    onFindDoctor: () -> Unit,
    onSignOut: () -> Unit,
    viewModel: PatientHomeViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()

    CedarScaffold(
        title = "Good day, ${viewModel.patientName}",
        actions = { TextButton(onClick = onSignOut) { Text("Sign out") } },
    ) { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = viewModel::load)
            is UiState.Success -> HomeContent(s.data, onFindDoctor, Modifier.padding(padding))
        }
    }
}

@Composable
private fun HomeContent(data: HomeData, onFindDoctor: () -> Unit, modifier: Modifier) {
    Column(
        modifier.fillMaxSize().verticalScroll(rememberScrollState()).padding(horizontal = 18.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        Spacer(Modifier.height(2.dp))

        // Next appointment
        SectionHeader("Next appointment")
        val appt = data.nextAppointment
        CedarCard {
            if (appt == null) {
                Text("No upcoming appointment.", color = CedarMuted, style = MaterialTheme.typography.bodyMedium)
                Spacer(Modifier.height(10.dp))
                TextButton(onClick = onFindDoctor) { Text("Find a doctor") }
            } else {
                Text(appt.reasonForVisit ?: "Consultation", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                Text(
                    listOfNotNull(appt.appointmentDate, appt.startTime).joinToString(" · ").ifBlank { "Scheduled" },
                    style = MaterialTheme.typography.bodyMedium, color = CedarMuted,
                )
                appt.status?.let { Text("Status: $it", style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
            }
        }

        // Today's medications
        SectionHeader("Today's medications")
        CedarCard {
            if (data.todaysMedications.isEmpty()) {
                Text("Nothing scheduled.", color = CedarMuted, style = MaterialTheme.typography.bodyMedium)
            } else {
                data.todaysMedications.forEach { med ->
                    Row(Modifier.fillMaxWidth().padding(vertical = 4.dp), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text(med.drugName ?: "Medication", color = CedarInk, style = MaterialTheme.typography.bodyLarge)
                        Text(med.frequency ?: "", color = CedarMuted, style = MaterialTheme.typography.bodyMedium)
                    }
                }
            }
        }

        // Billing
        SectionHeader("Billing")
        CedarCard {
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                Text("Amount due", color = CedarMuted, style = MaterialTheme.typography.bodyMedium)
                Text("$" + "%.2f".format(data.totalDue), color = CedarInk, fontWeight = FontWeight.Bold, style = MaterialTheme.typography.titleMedium)
            }
        }

        Spacer(Modifier.height(24.dp))
    }
}

package com.cedarview.hms.feature.doctor

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Description
import androidx.compose.material.icons.filled.Inbox
import androidx.compose.material.icons.filled.MonitorHeart
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Today
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.CedarTextField
import com.cedarview.hms.core.designsystem.component.EmptyView
import com.cedarview.hms.core.designsystem.component.ErrorView
import com.cedarview.hms.core.designsystem.component.LoadingView
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.CedarTab
import com.cedarview.hms.core.designsystem.component.TabbedShell
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.component.uiStateItems
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.data.remote.dto.DoctorApptDto
import com.cedarview.hms.feature.doctor.dashboard.DoctorDashboardScreen
import com.cedarview.hms.feature.doctor.patient.PatientProfileScreen
import com.cedarview.hms.feature.doctor.patient.PrescriptionScreen
import com.cedarview.hms.feature.shell.ProfilePane

@Composable
fun DoctorShell(
    onSignOut: () -> Unit,
    viewModel: DoctorAppViewModel = hiltViewModel(),
) {
    // In-shell drill-down: dashboard appointment → patient profile (D2) → prescribe (D4).
    var selectedAppt by remember { mutableStateOf<DoctorApptDto?>(null) }
    var prescribingVisitId by remember { mutableStateOf<Int?>(null) }

    val appt = selectedAppt
    val visitId = prescribingVisitId
    when {
        appt != null && visitId != null -> PrescriptionScreen(
            visitId = visitId,
            vm = viewModel,
            onBack = { prescribingVisitId = null },
            onSaved = { prescribingVisitId = null },
        )
        appt != null -> PatientProfileScreen(
            appt = appt,
            vm = viewModel,
            onBack = { selectedAppt = null },
            onPrescribe = { prescribingVisitId = it },
        )
        else -> {
            val tabs = listOf(
                CedarTab("Today", Icons.Filled.Today) { DoctorDashboardScreen(onSignOut = onSignOut, onOpenPatient = { selectedAppt = it }) },
                CedarTab("SOAP", Icons.Filled.Description) { SoapTab(viewModel) },
                CedarTab("Results", Icons.Filled.Inbox) { ResultsTab(viewModel) },
                CedarTab("Code Blue", Icons.Filled.MonitorHeart) { CodeBlueTab(viewModel) },
                CedarTab("Profile", Icons.Filled.Person) { ProfilePane(viewModel.doctorName, "Doctor", viewModel.roles, onSignOut) },
            )
            TabbedShell(tabs)
        }
    }
}

/** D5 — results to sign (the doctor's active lab worklist). */
@Composable
private fun ResultsTab(vm: DoctorAppViewModel) {
    LaunchedEffect(Unit) { vm.loadResultsInbox() }
    val state by vm.resultsInbox.collectAsStateWithLifecycle()
    CedarScaffold(title = "Results inbox") { padding ->
        LazyColumn(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            uiStateItems(state, vm::loadResultsInbox, "Nothing to review.") { o ->
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Column(Modifier.weight(1f)) {
                            Text(o.labOrderNo ?: "Lab order", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                            Text(listOfNotNull(o.patientName, o.clinicalIndication).joinToString(" · ").ifBlank { "—" }, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                        }
                        StatusPill(o.orderStatusLabel ?: "—", Tone.Warn)
                    }
                }
            }
        }
    }
}

@Composable
private fun SoapTab(vm: DoctorAppViewModel) {
    LaunchedEffect(Unit) { vm.loadSoap() }
    val state by vm.soap.collectAsStateWithLifecycle()
    var patientId by remember { mutableStateOf("") }
    var subjective by remember { mutableStateOf("") }
    var assessment by remember { mutableStateOf("") }
    var plan by remember { mutableStateOf("") }

    CedarScaffold(title = "SOAP notes") { padding ->
        LazyColumn(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            item {
                CedarCard {
                    SectionHeader("New note")
                    CedarTextField(patientId, { patientId = it }, "Patient ID", keyboardType = KeyboardType.Number)
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(subjective, { subjective = it }, "Subjective", singleLine = false)
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(assessment, { assessment = it }, "Assessment", singleLine = false)
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(plan, { plan = it }, "Plan", singleLine = false)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Save note", enabled = patientId.toIntOrNull() != null) {
                        patientId.toIntOrNull()?.let {
                            vm.createSoap(it, subjective, assessment, plan)
                            subjective = ""; assessment = ""; plan = ""
                        }
                    }
                }
                SectionHeader("Recent notes")
            }
            when (val s = state) {
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Success ->
                    if (s.data.isEmpty()) item { CedarCard { Text("No notes yet.", color = CedarMuted) } }
                    else items(s.data, key = { it.id ?: it.hashCode() }) { note ->
                        CedarCard {
                            Text(note.assessment ?: "SOAP note", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                            note.plan?.let { Text("Plan: $it", style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                            Text("Patient #${note.patientId ?: "—"} · ${note.notedAt ?: ""}", style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                        }
                    }
            }
        }
    }
}

@Composable
private fun CodeBlueTab(vm: DoctorAppViewModel) {
    LaunchedEffect(Unit) { vm.loadCodeBlue() }
    val state by vm.codeBlue.collectAsStateWithLifecycle()
    var location by remember { mutableStateOf("") }
    var reason by remember { mutableStateOf("") }

    CedarScaffold(title = "Code Blue") { padding ->
        LazyColumn(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp),
        ) {
            item {
                CedarCard {
                    SectionHeader("Raise alert")
                    CedarTextField(location, { location = it }, "Location (ward / bed)")
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(reason, { reason = it }, "Reason", singleLine = false)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Raise Code Blue") {
                        vm.raiseCodeBlue(location, reason); location = ""; reason = ""
                    }
                }
                SectionHeader("Active events")
            }
            uiStateItems(state, vm::loadCodeBlue, "No active events.") { e ->
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Column(Modifier.padding(end = 8.dp)) {
                            Text(e.reason ?: (e.eventType ?: "Event"), style = MaterialTheme.typography.titleMedium, color = CedarInk)
                            Text(
                                listOfNotNull(e.location, e.patient?.name).joinToString(" · ").ifBlank { "—" },
                                style = MaterialTheme.typography.bodyMedium, color = CedarMuted,
                            )
                        }
                        StatusPill(e.state ?: "active", Tone.Crit)
                    }
                }
            }
        }
    }
}

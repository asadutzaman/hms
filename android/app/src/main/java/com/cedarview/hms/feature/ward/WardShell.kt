package com.cedarview.hms.feature.ward

import androidx.compose.foundation.clickable
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
import androidx.compose.material.icons.filled.Bed
import androidx.compose.material.icons.filled.Inbox
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.Avatar
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.CedarTab
import com.cedarview.hms.core.designsystem.component.CedarTextField
import com.cedarview.hms.core.designsystem.component.EmptyView
import com.cedarview.hms.core.designsystem.component.ErrorView
import com.cedarview.hms.core.designsystem.component.GhostButton
import com.cedarview.hms.core.designsystem.component.LoadingView
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.TabbedShell
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.component.uiStateItems
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.data.remote.dto.AdmissionRowDto
import com.cedarview.hms.feature.shell.ProfilePane

@Composable
fun WardShell(onSignOut: () -> Unit, viewModel: WardViewModel = hiltViewModel()) {
    val tabs = listOf(
        CedarTab("Round", Icons.Filled.Bed) { RoundTab(viewModel) },
        CedarTab("Results", Icons.Filled.Inbox) { WardResultsTab(viewModel) },
        CedarTab("Profile", Icons.Filled.Person) { ProfilePane(viewModel.name, "Ward doctor (RMO)", viewModel.roles, onSignOut) },
    )
    TabbedShell(tabs)
}

@Composable
private fun RoundTab(vm: WardViewModel) {
    var selected by remember { mutableStateOf<AdmissionRowDto?>(null) }
    val current = selected
    if (current == null) WardRoundList(vm, onSelect = { selected = it })
    else WardAdmissionDetail(vm, current, onBack = { selected = null })
}

@Composable
private fun WardRoundList(vm: WardViewModel, onSelect: (AdmissionRowDto) -> Unit) {
    LaunchedEffect(Unit) { vm.loadDashboard(); vm.loadRoundList() }
    val dash by vm.dashboard.collectAsStateWithLifecycle()
    val state by vm.roundList.collectAsStateWithLifecycle()
    CedarScaffold(title = "Ward round") { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = vm::loadRoundList)
            is UiState.Success ->
                LazyColumn(
                    Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp),
                ) {
                    item {
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text("Inpatients on your wards", color = CedarMuted, style = MaterialTheme.typography.bodyMedium)
                                Text(((dash as? UiState.Success)?.data?.inpatients ?: s.data.size).toString(), style = MaterialTheme.typography.headlineMedium, color = CedarPrimary, fontWeight = FontWeight.Bold)
                            }
                        }
                    }
                    if (s.data.isEmpty()) item { CedarCard { Text("No inpatients.", color = CedarMuted) } }
                    else items(s.data, key = { it.id ?: it.hashCode() }) { adm ->
                        CedarCard(Modifier.clickable { onSelect(adm) }) {
                            Row {
                                Avatar(adm.patient?.name)
                                Column(Modifier.padding(start = 12.dp)) {
                                    Text(adm.patient?.name ?: "Patient", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                                    Text(
                                        listOfNotNull(adm.ward?.name, adm.bed?.bedNumber).joinToString(" · ").ifBlank { "Ward" },
                                        style = MaterialTheme.typography.bodyMedium, color = CedarMuted,
                                    )
                                }
                            }
                        }
                    }
                }
        }
    }
}

@Composable
private fun WardResultsTab(vm: WardViewModel) {
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
private fun WardAdmissionDetail(vm: WardViewModel, admission: AdmissionRowDto, onBack: () -> Unit) {
    val admissionId = admission.id ?: 0
    LaunchedEffect(admissionId) { vm.loadAdmission(admissionId) }
    val vitals by vm.vitals.collectAsStateWithLifecycle()
    val drugChart by vm.drugChart.collectAsStateWithLifecycle()
    val labs by vm.labOrders.collectAsStateWithLifecycle()
    val radiology by vm.radiologyOrders.collectAsStateWithLifecycle()
    val fluids by vm.fluidBalance.collectAsStateWithLifecycle()
    val reviews by vm.reviews.collectAsStateWithLifecycle()
    val discharge by vm.discharge.collectAsStateWithLifecycle()
    var note by remember { mutableStateOf("") }
    var plan by remember { mutableStateOf("") }

    CedarScaffold(title = admission.patient?.name ?: "Inpatient", onBack = onBack) { padding ->
        LazyColumn(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            item { SectionHeader("Latest vitals") }
            item {
                CedarCard {
                    when (val s = vitals) {
                        is UiState.Loading -> Text("Loading…", color = CedarMuted)
                        is UiState.Error -> Text(s.message, color = CedarMuted)
                        is UiState.Success -> {
                            val v = s.data.firstOrNull()
                            if (v == null) Text("No vitals recorded.", color = CedarMuted)
                            else {
                                Text("BP ${v.bpDisplay ?: "—"}  ·  HR ${v.pulseBpm ?: "—"}", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                Text("SpO₂ ${v.spo2Pct ?: "—"}%  ·  Temp ${v.temperatureC ?: "—"}°C  ·  RR ${v.respiratoryRate ?: "—"}", color = CedarMuted, style = MaterialTheme.typography.bodyMedium)
                            }
                        }
                    }
                }
            }

            item { SectionHeader("Drug chart") }
            when (val s = drugChart) {
                is UiState.Success ->
                    if (s.data.isEmpty()) item { CedarCard { Text("No medications.", color = CedarMuted) } }
                    else items(s.data, key = { "drug" + (it.id ?: it.hashCode()) }) { m ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Column {
                                    Text(m.drugName ?: "Medication", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                                    Text(listOfNotNull(m.route, m.frequency).joinToString(" · "), style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                                }
                                m.state?.let { StatusPill(it, Tone.Muted) }
                            }
                        }
                    }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
            }

            item { SectionHeader("Lab orders") }
            orderCards(labs, "No lab orders.")

            item { SectionHeader("Radiology orders") }
            when (val s = radiology) {
                is UiState.Success ->
                    if (s.data.isEmpty()) item { CedarCard { Text("No radiology orders.", color = CedarMuted) } }
                    else items(s.data, key = { "rad" + (it.id ?: it.hashCode()) }) { o ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Column(Modifier.weight(1f)) {
                                    Text(o.radOrderNo ?: "Radiology order", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                    o.clinicalIndication?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                                }
                                StatusPill(o.orderStatusLabel ?: "—", Tone.Muted)
                            }
                        }
                    }
                else -> Unit
            }

            item { SectionHeader("Fluid balance") }
            when (val s = fluids) {
                is UiState.Success ->
                    if (s.data.isEmpty()) item { CedarCard { Text("No fluid records.", color = CedarMuted) } }
                    else items(s.data, key = { "fl" + (it.date ?: it.hashCode()) }) { d ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text(d.date ?: "—", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                Text("In ${d.intake.toInt()} · Out ${d.output.toInt()} · Bal ${d.balance.toInt()} mL", style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                            }
                        }
                    }
                else -> Unit
            }

            item { SectionHeader("Daily review") }
            item {
                CedarCard {
                    CedarTextField(note, { note = it }, "Progress note", singleLine = false)
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(plan, { plan = it }, "Plan", singleLine = false)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Save review") { vm.addReview(admissionId, note, "", plan); note = ""; plan = "" }
                }
            }
            when (val s = reviews) {
                is UiState.Success ->
                    items(s.data, key = { "rev" + (it.id ?: it.hashCode()) }) { r ->
                        CedarCard {
                            Text(r.reviewDate ?: "Review", style = MaterialTheme.typography.labelSmall, color = CedarMuted)
                            Text(r.progressNote ?: "—", style = MaterialTheme.typography.bodyLarge, color = CedarInk)
                            r.plan?.let { Text("Plan: $it", style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                        }
                    }
                else -> Unit
            }

            item { SectionHeader("Discharge readiness (WD6/WD7)") }
            item {
                CedarCard {
                    if (discharge is UiState.Loading) {
                        Text("Loading…", color = CedarMuted)
                    } else {
                        // Success(null) / empty / error all mean "no signed summary yet".
                        val ds = (discharge as? UiState.Success)?.data
                        val summaryId = ds?.id
                        if (summaryId == null) {
                            Text("No discharge summary yet.", color = CedarMuted)
                            Spacer(Modifier.height(12.dp))
                            PrimaryButton("Generate draft") { vm.generateDischarge(admissionId) }
                        } else {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text(ds.summaryNo ?: "Draft", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                StatusPill(if (ds.isFinalized) "Signed" else "Draft", if (ds.isFinalized) Tone.Ok else Tone.Warn)
                            }
                            ds.dischargeDiagnosis?.let { Text("Dx: $it", style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                            ds.followUpInstructions?.let { Text("Follow-up: $it", style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                            if (!ds.isFinalized) {
                                Spacer(Modifier.height(12.dp))
                                GhostButton("Sign summary") { vm.signDischarge(summaryId, admissionId) }
                            }
                        }
                    }
                }
            }
            item { Spacer(Modifier.height(24.dp)) }
        }
    }
}

private fun androidx.compose.foundation.lazy.LazyListScope.orderCards(
    state: UiState<List<com.cedarview.hms.data.remote.dto.LabOrderDto>>,
    empty: String,
) {
    when (state) {
        is UiState.Success ->
            if (state.data.isEmpty()) item { CedarCard { Text(empty, color = CedarMuted) } }
            else items(state.data, key = { "lo" + (it.id ?: it.hashCode()) }) { o ->
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Column(Modifier.weight(1f)) {
                            Text(o.labOrderNo ?: "Lab order", color = CedarInk, fontWeight = FontWeight.SemiBold)
                            o.clinicalIndication?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                        }
                        StatusPill(o.orderStatusLabel ?: "—", Tone.Muted)
                    }
                }
            }
        else -> Unit
    }
}

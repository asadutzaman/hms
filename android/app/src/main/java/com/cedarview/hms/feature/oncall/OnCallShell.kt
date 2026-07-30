package com.cedarview.hms.feature.oncall

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
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Assignment
import androidx.compose.material.icons.filled.Dashboard
import androidx.compose.material.icons.filled.LocalHospital
import androidx.compose.material.icons.filled.MedicalServices
import androidx.compose.material.icons.filled.MoreHoriz
import androidx.compose.material.icons.filled.NotificationsActive
import androidx.compose.material.icons.filled.Warning
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.CedarTab
import com.cedarview.hms.core.designsystem.component.CedarTextField
import com.cedarview.hms.core.designsystem.component.ErrorView
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
import com.cedarview.hms.feature.shell.ProfilePane

@Composable
fun OnCallShell(onSignOut: () -> Unit, viewModel: OnCallViewModel = hiltViewModel()) {
    val tabs = listOf(
        CedarTab("Console", Icons.Filled.Dashboard) { ConsoleTab(viewModel) },
        CedarTab("Jobs", Icons.Filled.Assignment) { JobsTab(viewModel) },
        CedarTab("Bleeps", Icons.Filled.NotificationsActive) { BleepsTab(viewModel) },
        CedarTab("A-to-E", Icons.Filled.Warning) { AtoeTab(viewModel) },
        CedarTab("More", Icons.Filled.MoreHoriz) { MoreTab(viewModel, onSignOut) },
    )
    TabbedShell(tabs)
}

/** Groups Order sets (DD5), ED admissions (DD6), Handover (DD7) and Profile. */
@Composable
private fun MoreTab(vm: OnCallViewModel, onSignOut: () -> Unit) {
    var section by remember { mutableStateOf<String?>(null) }
    when (section) {
        "sets" -> OrderSetsScreen(vm) { section = null }
        "ed" -> EdBoardScreen(vm) { section = null }
        "handover" -> HandoverScreen(vm) { section = null }
        "profile" -> ProfilePane(vm.name, "On-call doctor", vm.roles, onSignOut, onBack = { section = null })
        else -> CedarScaffold(title = "More") { p ->
            LazyColumn(Modifier.fillMaxSize().padding(p).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                item { MoreRow("Order sets", "Bundle orders and apply them", Icons.Filled.MedicalServices) { section = "sets" } }
                item { MoreRow("ED admissions", "Emergency board and disposition", Icons.Filled.LocalHospital) { section = "ed" } }
                item { MoreRow("End-of-shift handover", "SBAR handover to the next team", Icons.Filled.Assignment) { section = "handover" } }
                item { MoreRow("Profile", "Session and sign out", Icons.Filled.Dashboard) { section = "profile" } }
            }
        }
    }
}

@Composable
private fun MoreRow(title: String, subtitle: String, icon: androidx.compose.ui.graphics.vector.ImageVector, onClick: () -> Unit) {
    CedarCard(Modifier.clickable(onClick = onClick)) {
        Row(Modifier.fillMaxWidth(), verticalAlignment = androidx.compose.ui.Alignment.CenterVertically) {
            androidx.compose.material3.Icon(icon, contentDescription = null, tint = CedarPrimary)
            Column(Modifier.padding(start = 12.dp)) {
                Text(title, style = MaterialTheme.typography.titleMedium, color = CedarInk)
                Text(subtitle, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
            }
        }
    }
}

@Composable
private fun ConsoleTab(vm: OnCallViewModel) {
    LaunchedEffect(Unit) { vm.loadConsole() }
    val state by vm.console.collectAsStateWithLifecycle()
    CedarScaffold(title = "On-call console") { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = vm::loadConsole)
            is UiState.Success -> Column(Modifier.fillMaxSize().padding(padding).padding(18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    StatCard("Open jobs", s.data.openJobs.toString(), Modifier.weight(1f))
                    StatCard("Unread bleeps", s.data.unreadBleeps.toString(), Modifier.weight(1f))
                }
                SectionHeader("Top jobs")
                s.data.jobs.take(5).forEach { j ->
                    CedarCard { Text(j.title ?: "Job", color = CedarInk, fontWeight = FontWeight.SemiBold) }
                }
            }
        }
    }
}

@Composable
private fun StatCard(label: String, value: String, modifier: Modifier = Modifier) {
    CedarCard(modifier) {
        Text(value, style = MaterialTheme.typography.headlineMedium, color = CedarPrimary, fontWeight = FontWeight.Bold)
        Text(label, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
    }
}

@Composable
private fun JobsTab(vm: OnCallViewModel) {
    LaunchedEffect(Unit) { vm.loadJobs() }
    val state by vm.jobs.collectAsStateWithLifecycle()
    var title by remember { mutableStateOf("") }
    CedarScaffold(title = "Job queue") { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            item {
                CedarCard {
                    SectionHeader("Add job")
                    CedarTextField(title, { title = it }, "Task title")
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Add", enabled = title.isNotBlank()) { vm.addJob(title, "routine"); title = "" }
                }
                SectionHeader("Queue")
            }
            uiStateItems(state, vm::loadJobs, "No open jobs.") { j ->
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Column(Modifier.weight(1f)) {
                            Text(j.title ?: "Job", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                            Text(listOfNotNull(j.priority, j.state, j.patient?.name).joinToString(" · "), style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                        }
                        j.id?.let { id ->
                            TextButton(onClick = { if (j.state == "open") vm.claimJob(id) else vm.completeJob(id) }) {
                                Text(if (j.state == "open") "Claim" else "Done")
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun BleepsTab(vm: OnCallViewModel) {
    LaunchedEffect(Unit) { vm.loadBleeps() }
    val state by vm.bleeps.collectAsStateWithLifecycle()
    var message by remember { mutableStateOf("") }
    CedarScaffold(title = "Bleeps") { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            item {
                CedarCard {
                    SectionHeader("Send bleep")
                    CedarTextField(message, { message = it }, "Message", singleLine = false)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Send", enabled = message.isNotBlank()) { vm.raiseBleep(message, "urgent"); message = "" }
                }
                SectionHeader("Incoming")
            }
            uiStateItems(state, vm::loadBleeps, "No bleeps.") { b ->
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Column(Modifier.weight(1f)) {
                            Text(b.message ?: "Bleep", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                            Text(listOfNotNull(b.priority, b.callback).joinToString(" · "), style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                        }
                        if (b.state == "sent") b.id?.let { id -> TextButton(onClick = { vm.acknowledgeBleep(id) }) { Text("Ack") } }
                        else StatusPill(b.state ?: "—", Tone.Muted)
                    }
                }
            }
        }
    }
}

@Composable
private fun AtoeTab(vm: OnCallViewModel) {
    LaunchedEffect(Unit) { vm.loadAssessments() }
    val state by vm.assessments.collectAsStateWithLifecycle()
    var impression by remember { mutableStateOf("") }
    var news2 by remember { mutableStateOf("") }
    CedarScaffold(title = "A-to-E") { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            item {
                CedarCard {
                    SectionHeader("New assessment")
                    CedarTextField(impression, { impression = it }, "Impression", singleLine = false)
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(news2, { news2 = it }, "NEWS2 score", keyboardType = KeyboardType.Number)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Save") { vm.addAssessment(impression, news2.toIntOrNull()); impression = ""; news2 = "" }
                }
                SectionHeader("Recent")
            }
            uiStateItems(state, vm::loadAssessments, "No assessments.") { a ->
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text(a.impression ?: "Assessment", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                        a.news2Score?.let { StatusPill("NEWS2 $it", if (it >= 5) Tone.Crit else Tone.Warn) }
                    }
                }
            }
        }
    }
}

@Composable
private fun OrderSetsScreen(vm: OnCallViewModel, onBack: () -> Unit) {
    LaunchedEffect(Unit) { vm.loadOrderSets() }
    val state by vm.orderSets.collectAsStateWithLifecycle()
    CedarScaffold(title = "Order sets", onBack = onBack) { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            uiStateItems(state, vm::loadOrderSets, "No order sets.") { o ->
                CedarCard {
                    Text(o.name ?: "Order set", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                    Text(listOfNotNull(o.category, o.description).joinToString(" · "), style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                }
            }
        }
    }
}

/** DD6 — ED admissions board (emergency visits with disposition). */
@Composable
private fun EdBoardScreen(vm: OnCallViewModel, onBack: () -> Unit) {
    LaunchedEffect(Unit) { vm.loadEdBoard() }
    val state by vm.edBoard.collectAsStateWithLifecycle()
    CedarScaffold(title = "ED admissions", onBack = onBack) { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            uiStateItems(state, vm::loadEdBoard, "No active ED visits.") { e ->
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Column(Modifier.weight(1f)) {
                            Text(e.patientName ?: (e.erVisitNo ?: "ED visit"), style = MaterialTheme.typography.titleMedium, color = CedarInk)
                            Text(listOfNotNull(e.chiefComplaint, e.arrivalMode).joinToString(" · ").ifBlank { "—" }, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                        }
                        StatusPill(e.disposition ?: e.erStatusLabel ?: "in ED", Tone.Warn)
                    }
                }
            }
        }
    }
}

/** DD7 — end-of-shift handover (SBAR). */
@Composable
private fun HandoverScreen(vm: OnCallViewModel, onBack: () -> Unit) {
    LaunchedEffect(Unit) { vm.loadHandovers() }
    val state by vm.handovers.collectAsStateWithLifecycle()
    var summary by remember { mutableStateOf("") }
    var shift by remember { mutableStateOf("") }
    CedarScaffold(title = "Handover", onBack = onBack) { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            item {
                CedarCard {
                    SectionHeader("New handover")
                    CedarTextField(shift, { shift = it }, "Shift (e.g. Night → Day)")
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(summary, { summary = it }, "SBAR summary", singleLine = false)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Submit handover") { vm.addHandover(summary, shift); summary = ""; shift = "" }
                }
                SectionHeader("Recent")
            }
            uiStateItems(state, vm::loadHandovers, "No handovers.") { h ->
                CedarCard {
                    Text(h.shiftLabel ?: "Handover", style = MaterialTheme.typography.labelSmall, color = CedarMuted)
                    Text(h.summary ?: "—", style = MaterialTheme.typography.bodyLarge, color = CedarInk)
                }
            }
        }
    }
}

package com.cedarview.hms.feature.nurse

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
import androidx.compose.foundation.lazy.LazyListScope
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Checklist
import androidx.compose.material.icons.filled.NotificationsActive
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.SwapHoriz
import androidx.compose.material.icons.filled.ViewList
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
import com.cedarview.hms.core.designsystem.component.Avatar
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.CedarTab
import com.cedarview.hms.core.designsystem.component.CedarTextField
import com.cedarview.hms.core.designsystem.component.ErrorView
import com.cedarview.hms.core.designsystem.component.GhostButton
import com.cedarview.hms.core.designsystem.component.LoadingView
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.TabbedShell
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.data.remote.dto.BedTileDto
import com.cedarview.hms.feature.shell.ProfilePane

@Composable
fun NurseShell(onSignOut: () -> Unit, viewModel: NurseViewModel = hiltViewModel()) {
    val tabs = listOf(
        CedarTab("Board", Icons.Filled.ViewList) { BoardTab(viewModel) },
        CedarTab("Rapid", Icons.Filled.NotificationsActive) { RapidTab(viewModel) },
        CedarTab("Tasks", Icons.Filled.Checklist) { TasksTab(viewModel) },
        CedarTab("Handover", Icons.Filled.SwapHoriz) { HandoverTab(viewModel) },
        CedarTab("Profile", Icons.Filled.Person) { ProfilePane(viewModel.name, "Nurse", viewModel.roles, onSignOut) },
    )
    TabbedShell(tabs)
}

// ── N1/N10 Board (ward patient wall) → admission detail ───────────────────────
@Composable
private fun BoardTab(vm: NurseViewModel) {
    var selected by remember { mutableStateOf<BedTileDto?>(null) }
    val current = selected
    if (current == null) BoardList(vm, onSelect = { selected = it })
    else NurseAdmissionDetail(vm, current, onBack = { selected = null })
}

@Composable
private fun BoardList(vm: NurseViewModel, onSelect: (BedTileDto) -> Unit) {
    LaunchedEffect(Unit) { vm.loadBoard() }
    val state by vm.board.collectAsStateWithLifecycle()
    CedarScaffold(title = "Shift board") { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = vm::loadBoard)
            is UiState.Success -> {
                val occupied = s.data.bedBoard.filter { it.admissionId != null }
                LazyColumn(
                    Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp),
                ) {
                    item {
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text("Inpatients on ward", color = CedarMuted, style = MaterialTheme.typography.bodyMedium)
                                Text(s.data.inpatients.toString(), style = MaterialTheme.typography.headlineMedium, color = CedarPrimary, fontWeight = FontWeight.Bold)
                            }
                        }
                    }
                    if (occupied.isEmpty()) item { CedarCard { Text("No occupied beds.", color = CedarMuted) } }
                    else items(occupied, key = { it.id ?: it.hashCode() }) { tile ->
                        CedarCard(Modifier.clickable(enabled = tile.admissionId != null) { onSelect(tile) }) {
                            Row {
                                Avatar(tile.patientName)
                                Column(Modifier.padding(start = 12.dp)) {
                                    Text(tile.patientName ?: "Patient", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                                    Text(listOfNotNull(tile.wardName, tile.bedNumber).joinToString(" · ").ifBlank { "Ward" }, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun NurseAdmissionDetail(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit) {
    val admissionId = tile.admissionId ?: 0
    LaunchedEffect(admissionId) { vm.loadAdmission(admissionId) }
    val mar by vm.mar.collectAsStateWithLifecycle()
    val vitals by vm.vitals.collectAsStateWithLifecycle()
    val fluid by vm.fluid.collectAsStateWithLifecycle()
    val notes by vm.notes.collectAsStateWithLifecycle()
    val checklist by vm.checklist.collectAsStateWithLifecycle()
    val barcode by vm.barcode.collectAsStateWithLifecycle()

    var scanMrn by remember { mutableStateOf("") }
    var sys by remember { mutableStateOf("") }
    var dia by remember { mutableStateOf("") }
    var pulse by remember { mutableStateOf("") }
    var temp by remember { mutableStateOf("") }
    var spo2 by remember { mutableStateOf("") }
    var rr by remember { mutableStateOf("") }
    var fluidType by remember { mutableStateOf("intake") }
    var fluidCat by remember { mutableStateOf("") }
    var fluidAmt by remember { mutableStateOf("") }
    var appearance by remember { mutableStateOf("") }
    var mobility by remember { mutableStateOf("") }
    var pain by remember { mutableStateOf("") }
    var carePlan by remember { mutableStateOf("") }
    var transferBed by remember { mutableStateOf("") }
    var transferReason by remember { mutableStateOf("") }

    CedarScaffold(title = tile.patientName ?: "Inpatient", onBack = onBack) { padding ->
        LazyColumn(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            // N6 Barcode / right-patient check
            item { SectionHeader("Verify patient (N6)") }
            item {
                CedarCard {
                    CedarTextField(scanMrn, { scanMrn = it }, "Scanned MRN")
                    Spacer(Modifier.height(8.dp))
                    PrimaryButton("Verify", enabled = scanMrn.isNotBlank()) { vm.verifyBarcode(admissionId, scanMrn.trim()) }
                    barcode?.let {
                        Spacer(Modifier.height(8.dp))
                        StatusPill(if (it.match) "Verified ✓" else "No match", if (it.match) Tone.Ok else Tone.Crit)
                    }
                }
            }

            // N2 MAR
            item { SectionHeader("Medication administration (N2)") }
            when (val s = mar) {
                is UiState.Success ->
                    if (s.data.isEmpty()) item { CedarCard { Text("No due medications.", color = CedarMuted) } }
                    else items(s.data, key = { "mar" + (it.id ?: it.hashCode()) }) { m ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Column(Modifier.weight(1f)) {
                                    Text(m.scheduledAt ?: "Scheduled dose", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                    Text(m.administrationStatusLabel ?: (m.administrationStatus ?: "due"), style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                                }
                                val mid = m.id
                                if (m.administeredAt == null && mid != null) {
                                    TextButton(onClick = { vm.recordMar(mid, "given", admissionId) }) { Text("Give") }
                                } else {
                                    StatusPill("Given", Tone.Ok)
                                }
                            }
                        }
                    }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
            }

            // N3 Record vitals
            item { SectionHeader("Record vitals (N3)") }
            item {
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        CedarTextField(sys, { sys = it }, "Systolic", modifier = Modifier.weight(1f), keyboardType = KeyboardType.Number)
                        CedarTextField(dia, { dia = it }, "Diastolic", modifier = Modifier.weight(1f), keyboardType = KeyboardType.Number)
                    }
                    Spacer(Modifier.height(8.dp))
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        CedarTextField(pulse, { pulse = it }, "Pulse", modifier = Modifier.weight(1f), keyboardType = KeyboardType.Number)
                        CedarTextField(temp, { temp = it }, "Temp °C", modifier = Modifier.weight(1f), keyboardType = KeyboardType.Decimal)
                    }
                    Spacer(Modifier.height(8.dp))
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        CedarTextField(spo2, { spo2 = it }, "SpO₂ %", modifier = Modifier.weight(1f), keyboardType = KeyboardType.Number)
                        CedarTextField(rr, { rr = it }, "Resp rate", modifier = Modifier.weight(1f), keyboardType = KeyboardType.Number)
                    }
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Save vitals") {
                        vm.recordVitals(admissionId, sys.toIntOrNull(), dia.toIntOrNull(), pulse.toIntOrNull(), temp.toDoubleOrNull(), spo2.toIntOrNull(), rr.toIntOrNull())
                        sys = ""; dia = ""; pulse = ""; temp = ""; spo2 = ""; rr = ""
                    }
                }
            }
            vitalHistory(vitals)

            // N7 Fluid balance
            item { SectionHeader("IV / fluid balance (N7)") }
            item {
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        GhostButton(if (fluidType == "intake") "Intake ✓" else "Intake", modifier = Modifier.weight(1f)) { fluidType = "intake" }
                        GhostButton(if (fluidType == "output") "Output ✓" else "Output", modifier = Modifier.weight(1f)) { fluidType = "output" }
                    }
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(fluidCat, { fluidCat = it }, "Category (e.g. IV, urine)")
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(fluidAmt, { fluidAmt = it }, "Amount (mL)", keyboardType = KeyboardType.Number)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Record fluid", enabled = fluidCat.isNotBlank() && fluidAmt.toDoubleOrNull() != null) {
                        vm.recordFluid(admissionId, fluidType, fluidCat.trim(), fluidAmt.toDouble())
                        fluidCat = ""; fluidAmt = ""
                    }
                }
            }
            when (val s = fluid) {
                is UiState.Success ->
                    items(s.data, key = { "fl" + (it.date ?: it.hashCode()) }) { d ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text(d.date ?: "—", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                Text("In ${d.intake.toInt()} · Out ${d.output.toInt()} · Bal ${d.balance.toInt()} mL", style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                            }
                        }
                    }
                else -> Unit
            }

            // N8 Nursing note
            item { SectionHeader("Nursing note (N8)") }
            item {
                CedarCard {
                    CedarTextField(appearance, { appearance = it }, "General appearance")
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(mobility, { mobility = it }, "Mobility")
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(pain, { pain = it }, "Pain assessment")
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(carePlan, { carePlan = it }, "Care plan notes", singleLine = false)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Save note") {
                        vm.addNursingNote(admissionId, appearance, mobility, pain, carePlan)
                        appearance = ""; mobility = ""; pain = ""; carePlan = ""
                    }
                }
            }
            when (val s = notes) {
                is UiState.Success ->
                    items(s.data, key = { "nn" + (it.id ?: it.hashCode()) }) { n ->
                        CedarCard {
                            Text(n.assessedAt ?: "Note", style = MaterialTheme.typography.labelSmall, color = CedarMuted)
                            Text(listOfNotNull(n.generalAppearance, n.mobilityStatus).joinToString(" · ").ifBlank { "—" }, color = CedarInk)
                            n.carePlanNotes?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                        }
                    }
                else -> Unit
            }

            // N11 Discharge checklist
            item { SectionHeader("Discharge checklist (N11)") }
            item { DischargeChecklistCard(vm, admissionId, checklist) }

            // N12 Transfer
            item { SectionHeader("Transfer patient (N12)") }
            item {
                CedarCard {
                    CedarTextField(transferBed, { transferBed = it }, "Destination bed ID", keyboardType = KeyboardType.Number)
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(transferReason, { transferReason = it }, "Reason")
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Transfer", enabled = transferBed.toIntOrNull() != null) {
                        transferBed.toIntOrNull()?.let { vm.transfer(admissionId, it, transferReason) }
                        transferBed = ""; transferReason = ""
                    }
                }
            }
            item { Spacer(Modifier.height(24.dp)) }
        }
    }
}

private fun LazyListScope.vitalHistory(state: UiState<List<com.cedarview.hms.data.remote.dto.VitalDto>>) {
    when (state) {
        is UiState.Success ->
            items(state.data.take(5), key = { "v" + (it.id ?: it.hashCode()) }) { v ->
                CedarCard {
                    Text(v.recordedAt ?: "Reading", style = MaterialTheme.typography.labelSmall, color = CedarMuted)
                    Text("BP ${v.bpDisplay ?: "—"} · HR ${v.pulseBpm ?: "—"} · SpO₂ ${v.spo2Pct ?: "—"}%", color = CedarInk)
                }
            }
        else -> Unit
    }
}

@Composable
private fun DischargeChecklistCard(
    vm: NurseViewModel,
    admissionId: Int,
    state: UiState<com.cedarview.hms.data.remote.dto.DischargeChecklistDto?>,
) {
    val defaults = listOf(
        "Medications reconciled", "Take-home meds dispensed", "Follow-up appointment booked",
        "Patient education given", "Transport arranged",
    )
    val existing = (state as? UiState.Success)?.data?.items
    val labels = remember(existing) {
        (existing?.mapNotNull { it.label }?.takeIf { it.isNotEmpty() } ?: defaults)
    }
    val checked = remember(existing) {
        mutableStateListFrom(labels) { label -> existing?.firstOrNull { it.label == label }?.done ?: false }
    }
    CedarCard {
        labels.forEachIndexed { i, label ->
            Row(Modifier.fillMaxWidth().clickable { checked[i] = !checked[i] }.padding(vertical = 6.dp), horizontalArrangement = Arrangement.SpaceBetween) {
                Text(label, color = CedarInk)
                StatusPill(if (checked[i]) "Done" else "Pending", if (checked[i]) Tone.Ok else Tone.Muted)
            }
        }
        Spacer(Modifier.height(12.dp))
        val items = labels.mapIndexed { i, label -> com.cedarview.hms.data.remote.dto.ChecklistItemDto(label = label, done = checked[i]) }
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
            GhostButton("Save", modifier = Modifier.weight(1f)) { vm.saveChecklist(admissionId, items, complete = false) }
            PrimaryButton("Mark complete", modifier = Modifier.weight(1f)) { vm.saveChecklist(admissionId, items, complete = true) }
        }
    }
}

private fun mutableStateListFrom(labels: List<String>, initial: (String) -> Boolean) =
    androidx.compose.runtime.mutableStateListOf<Boolean>().apply { labels.forEach { add(initial(it)) } }

// ── N9 Rapid response ─────────────────────────────────────────────────────────
@Composable
private fun RapidTab(vm: NurseViewModel) {
    LaunchedEffect(Unit) { vm.loadRapid() }
    val state by vm.rapid.collectAsStateWithLifecycle()
    var location by remember { mutableStateOf("") }
    var reason by remember { mutableStateOf("") }
    CedarScaffold(title = "Rapid response") { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            item {
                CedarCard {
                    SectionHeader("Escalate")
                    CedarTextField(location, { location = it }, "Location")
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(reason, { reason = it }, "Reason", singleLine = false)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Raise rapid response") { vm.raiseRapid(location, reason); location = ""; reason = "" }
                }
                SectionHeader("Active")
            }
            listState(state, vm::loadRapid, "No active events.") { e ->
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Text(e.reason ?: "Event", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                        StatusPill(e.state ?: "active", Tone.Crit)
                    }
                }
            }
        }
    }
}

// ── N5 Task timeline ──────────────────────────────────────────────────────────
@Composable
private fun TasksTab(vm: NurseViewModel) {
    LaunchedEffect(Unit) { vm.loadTasks() }
    val state by vm.tasks.collectAsStateWithLifecycle()
    CedarScaffold(title = "Tasks") { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            listState(state, vm::loadTasks, "No tasks assigned.") { t ->
                CedarCard {
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                        Column(Modifier.weight(1f)) {
                            Text(t.title ?: "Task", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                            t.description?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                        }
                        val tid = t.id
                        if (t.state != "done" && tid != null) TextButton(onClick = { vm.completeTask(tid) }) { Text("Done") }
                        else StatusPill(t.priority ?: "routine", priorityTone(t.priority))
                    }
                }
            }
        }
    }
}

// ── N4 Handover ───────────────────────────────────────────────────────────────
@Composable
private fun HandoverTab(vm: NurseViewModel) {
    LaunchedEffect(Unit) { vm.loadHandovers() }
    val state by vm.handovers.collectAsStateWithLifecycle()
    var summary by remember { mutableStateOf("") }
    var shift by remember { mutableStateOf("") }
    CedarScaffold(title = "Handover") { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            item {
                CedarCard {
                    SectionHeader("New handover")
                    CedarTextField(shift, { shift = it }, "Shift (e.g. Day → Night)")
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(summary, { summary = it }, "Summary", singleLine = false)
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Submit handover") { vm.addHandover(summary, shift); summary = ""; shift = "" }
                }
                SectionHeader("Recent")
            }
            listState(state, vm::loadHandovers, "No handovers.") { h ->
                CedarCard {
                    Text(h.shiftLabel ?: "Handover", style = MaterialTheme.typography.labelSmall, color = CedarMuted)
                    Text(h.summary ?: "—", style = MaterialTheme.typography.bodyLarge, color = CedarInk)
                }
            }
        }
    }
}

// Shared list-rendering helper for the nurse tabs (safe inside LazyColumn).
private fun <T> LazyListScope.listState(
    state: UiState<List<T>>,
    onRetry: () -> Unit,
    emptyMsg: String,
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
            val data = state.data
            if (data.isEmpty()) item { CedarCard { Text(emptyMsg, color = CedarMuted) } }
            else items(data) { row(it) }
        }
    }
}

private fun priorityTone(p: String?): Tone = when (p?.lowercase()) {
    "critical" -> Tone.Crit
    "urgent" -> Tone.Warn
    else -> Tone.Primary
}

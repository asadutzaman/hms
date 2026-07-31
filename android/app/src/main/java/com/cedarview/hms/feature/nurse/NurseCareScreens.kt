package com.cedarview.hms.feature.nurse

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBars
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.WarningAmber
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.BottomActionBar
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarPage
import com.cedarview.hms.core.designsystem.component.CedarTextField
import com.cedarview.hms.core.designsystem.component.ChoiceChip
import com.cedarview.hms.core.designsystem.component.CircleCheck
import com.cedarview.hms.core.designsystem.component.EmptyCard
import com.cedarview.hms.core.designsystem.component.EscalationBanner
import com.cedarview.hms.core.designsystem.component.GroupedCard
import com.cedarview.hms.core.designsystem.component.GroupedDivider
import com.cedarview.hms.core.designsystem.component.MetricTile
import com.cedarview.hms.core.designsystem.component.NoticeBanner
import com.cedarview.hms.core.designsystem.component.PatientBanner
import com.cedarview.hms.core.designsystem.component.PainScale
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.component.ProgressCard
import com.cedarview.hms.core.designsystem.component.RowAction
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.SectionTitle
import com.cedarview.hms.core.designsystem.component.SegmentButton
import com.cedarview.hms.core.designsystem.component.SoftButton
import com.cedarview.hms.core.designsystem.component.SquareCheck
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.theme.CedarBg
import com.cedarview.hms.core.designsystem.theme.CedarCrit
import com.cedarview.hms.core.designsystem.theme.CedarDivider
import com.cedarview.hms.core.designsystem.theme.CedarFaint
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarLine
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarOk
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.core.designsystem.theme.CedarScanBg
import com.cedarview.hms.core.designsystem.theme.CedarScanPane
import com.cedarview.hms.core.designsystem.theme.CedarWarn
import com.cedarview.hms.data.remote.dto.BedTileDto
import com.cedarview.hms.data.remote.dto.ChecklistItemDto
import com.cedarview.hms.data.remote.dto.MarDto

// Patient-scoped nurse screens. Each one is a design-doc screen: N2 medication
// round, N6 scan verification, N3 vitals, N7 fluids, N8 nursing note,
// N11 discharge checklist, N12 transfer and N9 escalation.

private fun BedTileDto.bedLine(): String =
    listOfNotNull(bedNumber?.let { "Bed $it" }, wardName).joinToString(" · ").ifBlank { "Inpatient" }

// ─────────────────────────────────────────────────────────────────────────────
// N2 — Medication round
// ─────────────────────────────────────────────────────────────────────────────
@Composable
internal fun MarScreen(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit, onScan: () -> Unit) {
    val admissionId = tile.admissionId ?: 0
    val state by vm.mar.collectAsStateWithLifecycle()
    val rows = (state as? UiState.Success)?.data.orEmpty()
    val signed = rows.count { it.isSigned }
    val due = rows.size - signed

    CedarPage(
        bottomBar = {
            BottomActionBar {
                PrimaryButton("Verify patient & scan", Modifier.weight(1f)) { onScan() }
            }
        },
    ) {
        item {
            ScreenHeaderRow(
                title = "Medication round",
                subtitle = if (due > 0) "$due due · ${tile.wardName ?: "Ward"}" else "All signed · ${tile.wardName ?: "Ward"}",
                onBack = onBack,
            )
        }
        item {
            PatientBanner(
                name = tile.patientName ?: "Inpatient",
                detail = listOfNotNull(tile.bedNumber?.let { "Bed $it" }, tile.admissionNo).joinToString(" · "),
            )
        }
        if (rows.isNotEmpty()) {
            item {
                ProgressCard(
                    "Round progress",
                    "$signed of ${rows.size}",
                    if (rows.isEmpty()) 0f else signed.toFloat() / rows.size,
                )
            }
        }

        when (val s = state) {
            is UiState.Loading -> item { EmptyCard("Loading the drug chart…") }
            is UiState.Error -> item { RetryCard(s.message) { vm.loadAdmission(admissionId) } }
            is UiState.Success ->
                if (rows.isEmpty()) {
                    item { EmptyCard("No scheduled doses for this admission.") }
                } else {
                    items(rows.size) { i ->
                        val med = rows[i]
                        val id = med.id
                        MedicationRow(med, onGive = { if (id != null) vm.recordMar(id, "given", admissionId) })
                    }
                }
        }

        item {
            NoticeBanner(
                title = "Scan each medication before giving.",
                text = "High-alert drugs such as insulin need a second nurse to co-sign.",
                tone = Tone.Warn,
            )
        }
    }
}

@Composable
private fun MedicationRow(med: MarDto, onGive: () -> Unit) {
    CedarCard(padding = 14.dp) {
        Row(
            Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.spacedBy(12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Column(Modifier.width(50.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                Text(clockTime(med.scheduledAt), fontSize = 13.sp, fontWeight = FontWeight.Bold, color = CedarInk)
                Text(
                    if (med.isSigned) "DONE" else "DUE",
                    fontSize = 10.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = CedarFaint,
                    letterSpacing = 0.4.sp,
                )
            }
            Box(Modifier.width(1.dp).height(36.dp).background(CedarDivider))
            Column(Modifier.weight(1f)) {
                Text(med.displayName, fontSize = 14.sp, fontWeight = FontWeight.SemiBold, color = CedarInk)
                val sub = med.displayRoute.ifBlank { med.administrationStatusLabel ?: "Scheduled" }
                Text(sub, fontSize = 12.sp, color = CedarMuted)
            }
            when {
                med.isGiven -> StatusPill("Given", Tone.Ok)
                med.isSigned -> StatusPill(med.administrationStatusLabel ?: "Signed", Tone.Warn)
                else -> RowAction("Give", onClick = onGive)
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// N6 — Scan verification (five rights)
// ─────────────────────────────────────────────────────────────────────────────
@Composable
internal fun VerifyScreen(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit) {
    val admissionId = tile.admissionId ?: 0
    val result by vm.barcode.collectAsStateWithLifecycle()
    var code by remember { mutableStateOf("") }
    val verified = result?.match == true

    Scaffold(
        containerColor = CedarScanBg,
        contentWindowInsets = WindowInsets.statusBars,
        bottomBar = {
            Column(Modifier.background(CedarScanBg)) {
                Box(Modifier.fillMaxWidth().height(1.dp).background(Color.White.copy(alpha = 0.1f)))
                Row(Modifier.padding(horizontal = 20.dp, vertical = 12.dp)) {
                    PrimaryButton(
                        if (verified) "Continue" else "Verify wristband",
                        Modifier.weight(1f),
                        enabled = verified || code.isNotBlank(),
                        container = if (verified) CedarOk else CedarPrimary,
                    ) {
                        if (verified) onBack() else vm.verifyBarcode(admissionId, code.trim())
                    }
                }
            }
        },
    ) { padding ->
        Column(
            Modifier.fillMaxSize().padding(padding).verticalScroll(rememberScrollState()),
        ) {
            Row(
                Modifier.fillMaxWidth().padding(horizontal = 20.dp, vertical = 14.dp),
                horizontalArrangement = Arrangement.spacedBy(12.dp),
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Box(
                    Modifier.size(38.dp).background(Color.White.copy(alpha = 0.1f), CircleShape).clickable(onClick = onBack),
                    contentAlignment = Alignment.Center,
                ) {
                    Icon(Icons.Filled.Close, "Close", tint = Color.White, modifier = Modifier.size(16.dp))
                }
                Column(Modifier.weight(1f)) {
                    Text("Scan wristband", fontSize = 16.sp, fontWeight = FontWeight.SemiBold, color = Color.White)
                    Text(
                        if (verified) "Patient confirmed" else "Step 1 of 2 · confirm the right patient",
                        fontSize = 12.sp,
                        color = Color.White.copy(alpha = 0.5f),
                    )
                }
            }

            ScannerViewport(verified)

            Column(Modifier.padding(horizontal = 20.dp, vertical = 18.dp), verticalArrangement = Arrangement.spacedBy(11.dp)) {
                Text(
                    "FIVE RIGHTS",
                    fontSize = 11.sp,
                    fontWeight = FontWeight.SemiBold,
                    letterSpacing = 0.7.sp,
                    color = Color.White.copy(alpha = 0.4f),
                )
                RightRow(verified, "Right patient", tile.patientName ?: "Inpatient")
                RightRow(verified, "Right bed", tile.bedLine())
                RightRow(false, "Right drug", "confirm against the label")
                RightRow(false, "Right dose", "confirm against the chart")
                RightRow(false, "Right time", "confirm the due slot")

                Spacer(Modifier.height(4.dp))
                OutlinedTextField(
                    value = code,
                    onValueChange = { code = it },
                    label = { Text("Wristband / MRN", color = Color.White.copy(alpha = 0.5f)) },
                    singleLine = true,
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(13.dp),
                    textStyle = MaterialTheme.typography.bodyLarge.copy(color = Color.White),
                    colors = OutlinedTextFieldDefaults.colors(
                        unfocusedBorderColor = Color.White.copy(alpha = 0.2f),
                        focusedBorderColor = CedarPrimary,
                        unfocusedContainerColor = Color.White.copy(alpha = 0.06f),
                        focusedContainerColor = Color.White.copy(alpha = 0.06f),
                        cursorColor = Color.White,
                    ),
                )

                result?.let {
                    Surface(
                        Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(13.dp),
                        color = if (it.match) CedarOk.copy(alpha = 0.16f) else CedarCrit.copy(alpha = 0.16f),
                    ) {
                        Row(
                            Modifier.padding(horizontal = 13.dp, vertical = 11.dp),
                            horizontalArrangement = Arrangement.spacedBy(9.dp),
                            verticalAlignment = Alignment.CenterVertically,
                        ) {
                            Icon(
                                if (it.match) Icons.Filled.Check else Icons.Filled.WarningAmber,
                                contentDescription = null,
                                tint = if (it.match) Color(0xFF86EFAC) else Color(0xFFFCA5A5),
                                modifier = Modifier.size(16.dp),
                            )
                            Text(
                                if (it.match) "Wristband matches this admission." else "This code does not match the patient in bed ${tile.bedNumber ?: "—"}.",
                                fontSize = 12.5.sp,
                                color = if (it.match) Color(0xFF86EFAC) else Color(0xFFFCA5A5),
                                lineHeight = 18.sp,
                            )
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun ScannerViewport(verified: Boolean) {
    Box(Modifier.fillMaxWidth().height(240.dp).background(CedarScanPane), contentAlignment = Alignment.Center) {
        Box(Modifier.width(230.dp).height(140.dp)) {
            val accent = if (verified) CedarOk else CedarPrimary
            ScanCorner(Alignment.TopStart, accent, top = true, start = true)
            ScanCorner(Alignment.TopEnd, accent, top = true, start = false)
            ScanCorner(Alignment.BottomStart, accent, top = false, start = true)
            ScanCorner(Alignment.BottomEnd, accent, top = false, start = false)
            Box(
                Modifier.align(Alignment.Center).fillMaxWidth().padding(horizontal = 16.dp)
                    .height(2.dp).background(accent),
            )
        }
        Text(
            if (verified) "Verified" else "Hold steady over the barcode",
            fontSize = 13.sp,
            color = Color.White.copy(alpha = 0.6f),
            modifier = Modifier.align(Alignment.BottomCenter).padding(bottom = 16.dp),
        )
    }
}

@Composable
private fun ScanCorner(alignment: Alignment, color: Color, top: Boolean, start: Boolean) {
    Box(Modifier.fillMaxSize()) {
        Box(Modifier.align(alignment).size(34.dp)) {
            Box(Modifier.fillMaxWidth().height(3.dp).align(if (top) Alignment.TopStart else Alignment.BottomStart).background(color))
            Box(Modifier.width(3.dp).fillMaxHeight().align(if (start) Alignment.TopStart else Alignment.TopEnd).background(color))
        }
    }
}

@Composable
private fun RightRow(checked: Boolean, label: String, detail: String) {
    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(11.dp), verticalAlignment = Alignment.CenterVertically) {
        CircleCheck(checked)
        Text(
            "$label — $detail",
            fontSize = 14.sp,
            color = if (checked) Color.White else Color.White.copy(alpha = 0.55f),
            modifier = Modifier.weight(1f),
        )
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// N3 — Record vitals
// ─────────────────────────────────────────────────────────────────────────────
@Composable
internal fun VitalsScreen(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit, onEscalate: () -> Unit) {
    val admissionId = tile.admissionId ?: 0
    val state by vm.vitals.collectAsStateWithLifecycle()
    val history = (state as? UiState.Success)?.data.orEmpty()
    val latest = history.firstOrNull()
    val previous = history.getOrNull(1)

    var sys by remember { mutableStateOf("") }
    var dia by remember { mutableStateOf("") }
    var pulse by remember { mutableStateOf("") }
    var temp by remember { mutableStateOf("") }
    var spo2 by remember { mutableStateOf("") }
    var rr by remember { mutableStateOf("") }
    var pain by remember { mutableStateOf<Int?>(null) }

    val enteredSpo2 = spo2.toIntOrNull()
    val breach = enteredSpo2 != null && enteredSpo2 < SPO2_ESCALATION_THRESHOLD
    val hasEntry = listOf(sys, dia, pulse, temp, spo2, rr).any { it.isNotBlank() } || pain != null

    CedarPage(
        bottomBar = {
            BottomActionBar {
                PrimaryButton(
                    if (breach) "Save & escalate" else "Save observations",
                    Modifier.weight(1f),
                    enabled = hasEntry,
                    container = if (breach) CedarCrit else CedarPrimary,
                ) {
                    vm.recordVitals(
                        admissionId,
                        sys.toIntOrNull(), dia.toIntOrNull(), pulse.toIntOrNull(),
                        temp.toDoubleOrNull(), enteredSpo2, rr.toIntOrNull(), pain,
                    )
                    val escalate = breach
                    sys = ""; dia = ""; pulse = ""; temp = ""; spo2 = ""; rr = ""; pain = null
                    if (escalate) onEscalate()
                }
            }
        },
    ) {
        item {
            ScreenHeaderRow(
                title = "Record vitals",
                subtitle = "${tile.patientName ?: "Inpatient"} · ${tile.bedLine()}",
                onBack = onBack,
            ) {
                latest?.spo2Pct?.let {
                    StatusPill("SpO₂ $it%", if (it < SPO2_ESCALATION_THRESHOLD) Tone.Crit else Tone.Ok)
                }
            }
        }

        if (latest != null) {
            item { SectionHeader("Last reading · ${stampLabel(latest.recordedAt)}") }
            item {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    MetricTile(
                        "BP · MMHG", latest.bpText(), Modifier.weight(1f),
                        previous = previous?.let { previousLabel(it.bpText()) },
                        note = trendNote(latest.bpSystolic, previous?.bpSystolic, higherIsWorse = true)?.first,
                        tone = trendNote(latest.bpSystolic, previous?.bpSystolic, higherIsWorse = true)?.second ?: Tone.Muted,
                    )
                    MetricTile(
                        "HR · BPM", latest.pulseBpm?.toString() ?: "—", Modifier.weight(1f),
                        previous = previous?.pulseBpm?.let { previousLabel(it) },
                        note = trendNote(latest.pulseBpm, previous?.pulseBpm, higherIsWorse = true)?.first,
                        tone = trendNote(latest.pulseBpm, previous?.pulseBpm, higherIsWorse = true)?.second ?: Tone.Muted,
                    )
                }
            }
            item {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                    val low = (latest.spo2Pct ?: 100) < SPO2_ESCALATION_THRESHOLD
                    MetricTile(
                        "SpO₂ · %", latest.spo2Pct?.let { "$it" } ?: "—", Modifier.weight(1f),
                        previous = previous?.spo2Pct?.let { previousLabel(it) },
                        note = if (low) "Below target — escalate" else null,
                        tone = if (low) Tone.Crit else Tone.Muted,
                    )
                    MetricTile(
                        "TEMP · °C", latest.temperatureC?.toString() ?: "—", Modifier.weight(1f),
                        previous = previous?.temperatureC?.let { previousLabel(it) },
                        note = latest.respiratoryRate?.let { "RR $it" },
                        tone = Tone.Muted,
                    )
                }
            }
        }

        item { SectionTitle("New reading") }
        item {
            CedarCard {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    CedarTextField(sys, { sys = it }, "Systolic", Modifier.weight(1f), KeyboardType.Number)
                    CedarTextField(dia, { dia = it }, "Diastolic", Modifier.weight(1f), KeyboardType.Number)
                }
                Spacer(Modifier.height(8.dp))
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    CedarTextField(pulse, { pulse = it }, "Pulse", Modifier.weight(1f), KeyboardType.Number)
                    CedarTextField(temp, { temp = it }, "Temp °C", Modifier.weight(1f), KeyboardType.Decimal)
                }
                Spacer(Modifier.height(8.dp))
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    CedarTextField(spo2, { spo2 = it }, "SpO₂ %", Modifier.weight(1f), KeyboardType.Number)
                    CedarTextField(rr, { rr = it }, "Resp rate", Modifier.weight(1f), KeyboardType.Number)
                }
            }
        }

        item {
            CedarCard {
                SectionHeader("Pain score")
                Spacer(Modifier.height(10.dp))
                PainScale(pain) { pain = it }
            }
        }

        if (breach) {
            item {
                EscalationBanner(
                    badge = enteredSpo2.toString(),
                    badgeCaption = "SpO₂",
                    title = "Rapid response criteria met",
                    body = "SpO₂ below $SPO2_ESCALATION_THRESHOLD%. Saving takes you straight to the escalation draft.",
                )
            }
        }

        if (history.size > 1) {
            item { SectionTitle("Earlier observations") }
            item {
                GroupedCard {
                    history.drop(1).take(5).forEachIndexed { i, v ->
                        if (i > 0) GroupedDivider()
                        HistoryRow(
                            clockTime(v.recordedAt),
                            "BP ${v.bpText()} · HR ${v.pulseBpm ?: "—"} · SpO₂ ${v.spo2Pct ?: "—"}%",
                            listOfNotNull(v.temperatureC?.let { "Temp $it" }, v.respiratoryRate?.let { "RR $it" }).joinToString(" · ").ifBlank { null },
                        )
                    }
                }
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// N7 — IV & fluid balance
// ─────────────────────────────────────────────────────────────────────────────
private val FLUID_CATEGORIES = listOf("IV fluid", "IV medication", "Oral", "Urine", "Drain", "Vomit")

@Composable
internal fun FluidsScreen(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit) {
    val admissionId = tile.admissionId ?: 0
    val state by vm.fluid.collectAsStateWithLifecycle()
    val days = (state as? UiState.Success)?.data.orEmpty()
    val today = days.firstOrNull()

    var type by remember { mutableStateOf("intake") }
    var category by remember { mutableStateOf(FLUID_CATEGORIES.first()) }
    var amount by remember { mutableStateOf("") }

    CedarPage(
        bottomBar = {
            BottomActionBar {
                PrimaryButton("Log fluid", Modifier.weight(1f), enabled = amount.toDoubleOrNull() != null) {
                    amount.toDoubleOrNull()?.let { vm.recordFluid(admissionId, type, category, it) }
                    amount = ""
                }
            }
        },
    ) {
        item {
            ScreenHeaderRow(
                title = "IV & fluid balance",
                subtitle = "${tile.patientName ?: "Inpatient"} · ${tile.bedLine()}",
                onBack = onBack,
            )
        }

        item {
            CedarCard {
                Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
                    Column(Modifier.weight(1f)) {
                        SectionHeader("Balance today")
                        Text(
                            "${today?.balance?.toInt() ?: 0} mL",
                            fontSize = 26.sp,
                            fontWeight = FontWeight.Bold,
                            color = if ((today?.balance ?: 0.0) < 0) CedarWarn else CedarInk,
                        )
                    }
                    StatusPill(today?.date ?: "No entries", Tone.Muted)
                }
                Spacer(Modifier.height(14.dp))
                val peak = maxOf(today?.intake ?: 0.0, today?.output ?: 0.0, 1.0)
                BalanceBar("Intake", today?.intake ?: 0.0, peak, CedarPrimary)
                Spacer(Modifier.height(9.dp))
                BalanceBar("Output", today?.output ?: 0.0, peak, CedarWarn)
            }
        }

        item { SectionTitle("Record") }
        item {
            CedarCard {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    SegmentButton("Intake", type == "intake", Modifier.weight(1f)) { type = "intake" }
                    SegmentButton("Output", type == "output", Modifier.weight(1f), tone = Tone.Warn) { type = "output" }
                }
                Spacer(Modifier.height(12.dp))
                SectionHeader("Category")
                Spacer(Modifier.height(8.dp))
                ChipFlow(FLUID_CATEGORIES, category) { category = it }
                Spacer(Modifier.height(12.dp))
                CedarTextField(amount, { amount = it }, "Amount (mL)", keyboardType = KeyboardType.Number)
            }
        }

        if (days.isNotEmpty()) {
            item { SectionTitle("History") }
            item {
                GroupedCard {
                    days.forEachIndexed { i, d ->
                        if (i > 0) GroupedDivider()
                        Row(
                            Modifier.fillMaxWidth().padding(horizontal = 15.dp, vertical = 13.dp),
                            horizontalArrangement = Arrangement.SpaceBetween,
                            verticalAlignment = Alignment.CenterVertically,
                        ) {
                            Text(d.date ?: "—", fontSize = 14.sp, fontWeight = FontWeight.SemiBold, color = CedarInk)
                            Text(
                                "In ${d.intake.toInt()} · Out ${d.output.toInt()} · Bal ${d.balance.toInt()} mL",
                                fontSize = 12.sp,
                                color = CedarMuted,
                            )
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun BalanceBar(label: String, value: Double, peak: Double, color: Color) {
    Column(Modifier.fillMaxWidth()) {
        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
            Text(label, fontSize = 12.sp, fontWeight = FontWeight.SemiBold, color = CedarMuted)
            Text("${value.toInt()} mL", fontSize = 12.sp, fontWeight = FontWeight.Bold, color = color)
        }
        Spacer(Modifier.height(6.dp))
        Box(Modifier.fillMaxWidth().height(8.dp).background(CedarBg, RoundedCornerShape(4.dp))) {
            Box(
                Modifier.fillMaxWidth((value / peak).toFloat().coerceIn(0f, 1f)).fillMaxHeight()
                    .background(color, RoundedCornerShape(4.dp)),
            )
        }
    }
}

/** Wrapping row of single-select chips (Compose BOM here predates FlowRow stability). */
@Composable
internal fun ChipFlow(options: List<String>, selected: String?, onSelect: (String) -> Unit) {
    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
        options.chunked(3).forEach { row ->
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                row.forEach { option ->
                    ChoiceChip(option, option == selected, Modifier.weight(1f)) { onSelect(option) }
                }
                repeat(3 - row.size) { Spacer(Modifier.weight(1f)) }
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// N8 — Nursing note
// ─────────────────────────────────────────────────────────────────────────────
private val NOTE_TEMPLATES = listOf("Respiratory", "Pain", "Mobility", "Wound", "Intake/output", "Family")

@Composable
internal fun NursingNoteScreen(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit) {
    val admissionId = tile.admissionId ?: 0
    val state by vm.notes.collectAsStateWithLifecycle()
    val notes = (state as? UiState.Success)?.data.orEmpty()

    var appearance by remember { mutableStateOf("") }
    var mobility by remember { mutableStateOf("") }
    var pain by remember { mutableStateOf("") }
    var carePlan by remember { mutableStateOf("") }

    CedarPage(
        bottomBar = {
            BottomActionBar {
                PrimaryButton(
                    "Sign & file note",
                    Modifier.weight(1f),
                    enabled = listOf(appearance, mobility, pain, carePlan).any { it.isNotBlank() },
                ) {
                    vm.addNursingNote(admissionId, appearance, mobility, pain, carePlan)
                    appearance = ""; mobility = ""; pain = ""; carePlan = ""
                }
            }
        },
    ) {
        item {
            ScreenHeaderRow(
                title = "Nursing note",
                subtitle = "${tile.patientName ?: "Inpatient"} · ${tile.bedLine()}",
                onBack = onBack,
            )
        }

        item { SectionHeader("Quick add") }
        item {
            ChipFlow(NOTE_TEMPLATES, null) { template ->
                carePlan = (carePlan.trimEnd().takeIf { it.isNotBlank() }?.plus("\n") ?: "") + "$template: "
            }
        }

        item {
            CedarCard {
                CedarTextField(appearance, { appearance = it }, "General appearance")
                Spacer(Modifier.height(8.dp))
                CedarTextField(mobility, { mobility = it }, "Mobility")
                Spacer(Modifier.height(8.dp))
                CedarTextField(pain, { pain = it }, "Pain assessment")
                Spacer(Modifier.height(8.dp))
                CedarTextField(carePlan, { carePlan = it }, "Care plan notes", singleLine = false, minLines = 4)
            }
        }

        if (notes.isNotEmpty()) {
            item {
                GroupedCard {
                    GroupedHeaderRow("Earlier notes")
                    notes.take(6).forEach { note ->
                        GroupedDivider()
                        HistoryRow(
                            clockTime(note.assessedAt),
                            listOfNotNull(note.generalAppearance, note.mobilityStatus, note.painAssessment)
                                .joinToString(" · ").ifBlank { "Assessment recorded" },
                            note.carePlanNotes,
                        )
                    }
                }
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// N11 — Discharge checklist
// ─────────────────────────────────────────────────────────────────────────────
private val DEFAULT_CHECKLIST = listOf(
    "Medications reconciled",
    "Take-home meds dispensed",
    "Follow-up appointment booked",
    "Patient education given",
    "Transport arranged",
)

@Composable
internal fun DischargeChecklistScreen(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit) {
    val admissionId = tile.admissionId ?: 0
    val state by vm.checklist.collectAsStateWithLifecycle()
    val existing = (state as? UiState.Success)?.data?.items

    val labels = remember(existing) {
        existing?.mapNotNull { it.label }?.takeIf { it.isNotEmpty() } ?: DEFAULT_CHECKLIST
    }
    val checked = remember(existing) {
        mutableStateListOf<Boolean>().apply {
            labels.forEach { label -> add(existing?.firstOrNull { it.label == label }?.done ?: false) }
        }
    }
    val done = checked.count { it }
    val payload = { labels.mapIndexed { i, label -> ChecklistItemDto(label = label, done = checked[i]) } }

    CedarPage(
        bottomBar = {
            BottomActionBar {
                SoftButton("Save", Modifier.weight(1f)) { vm.saveChecklist(admissionId, payload(), complete = false) }
                PrimaryButton(
                    "Mark complete",
                    Modifier.weight(1f),
                    enabled = done == labels.size,
                    container = CedarOk,
                ) { vm.saveChecklist(admissionId, payload(), complete = true) }
            }
        },
    ) {
        item {
            ScreenHeaderRow(
                title = "Discharge checklist",
                subtitle = "${tile.patientName ?: "Inpatient"} · ${tile.bedLine()}",
                onBack = onBack,
            )
        }
        item { ProgressCard("Checklist", "$done of ${labels.size}", done.toFloat() / labels.size) }
        item {
            GroupedCard {
                labels.forEachIndexed { i, label ->
                    if (i > 0) GroupedDivider()
                    Row(
                        Modifier.fillMaxWidth().clickable { checked[i] = !checked[i] }
                            .padding(horizontal = 15.dp, vertical = 13.dp),
                        horizontalArrangement = Arrangement.spacedBy(13.dp),
                        verticalAlignment = Alignment.CenterVertically,
                    ) {
                        SquareCheck(checked[i]) { checked[i] = !checked[i] }
                        Text(label, Modifier.weight(1f), fontSize = 14.sp, fontWeight = FontWeight.Medium, color = CedarInk)
                        if (checked[i]) StatusPill("Done", Tone.Ok)
                    }
                }
            }
        }
        item {
            NoticeBanner(
                text = "Every item must be ticked before the discharge can be marked complete.",
                tone = Tone.Warn,
            )
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// N12 — Transfer
// ─────────────────────────────────────────────────────────────────────────────
@Composable
internal fun TransferScreen(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit) {
    val admissionId = tile.admissionId ?: 0
    var bed by remember { mutableStateOf("") }
    var reason by remember { mutableStateOf("") }
    var sent by remember { mutableStateOf(false) }

    CedarPage(
        bottomBar = {
            BottomActionBar {
                PrimaryButton("Transfer patient", Modifier.weight(1f), enabled = bed.toIntOrNull() != null) {
                    bed.toIntOrNull()?.let { vm.transfer(admissionId, it, reason) }
                    bed = ""; reason = ""; sent = true
                }
            }
        },
    ) {
        item {
            ScreenHeaderRow(title = "Transfer patient", subtitle = tile.patientName, onBack = onBack)
        }
        item {
            PatientBanner(
                name = tile.patientName ?: "Inpatient",
                detail = "Currently in ${tile.bedLine()}",
            )
        }
        item {
            CedarCard {
                CedarTextField(bed, { bed = it }, "Destination bed ID", keyboardType = KeyboardType.Number)
                Spacer(Modifier.height(8.dp))
                CedarTextField(reason, { reason = it }, "Reason for transfer", singleLine = false, minLines = 3)
            }
        }
        if (sent) {
            item { NoticeBanner(text = "Transfer submitted. The bed board updates once the move is confirmed.", tone = Tone.Ok) }
        }
        item {
            NoticeBanner(
                text = "Transfers are logged against your ID and notify the receiving nurse.",
                tone = Tone.Muted,
            )
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// N9 — Escalate (patient-scoped rapid response)
// ─────────────────────────────────────────────────────────────────────────────
@Composable
internal fun EscalateScreen(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit) {
    val vitalsState by vm.vitals.collectAsStateWithLifecycle()
    val latest = (vitalsState as? UiState.Success)?.data?.firstOrNull()
    val spo2 = latest?.spo2Pct
    val critical = spo2 != null && spo2 < SPO2_ESCALATION_THRESHOLD

    val vitalsLine = latest?.let {
        "SpO₂ ${it.spo2Pct ?: "—"}% · HR ${it.pulseBpm ?: "—"} · RR ${it.respiratoryRate ?: "—"} · BP ${it.bpText()}"
    } ?: "No observations recorded yet."

    var situation by remember { mutableStateOf("${tile.patientName ?: "Patient"}, ${tile.bedLine()} — ") }
    var background by remember { mutableStateOf(tile.admissionNo?.let { "Admission $it. " } ?: "") }
    var assessment by remember { mutableStateOf(vitalsLine) }
    var recommendation by remember { mutableStateOf("Bedside review now.") }
    var sent by remember { mutableStateOf(false) }

    CedarPage(
        bottomBar = {
            BottomActionBar {
                PrimaryButton("Send escalation now", Modifier.weight(1f), container = CedarCrit) {
                    vm.raiseRapid(
                        location = tile.bedLine(),
                        reason = listOf(
                            "S: $situation",
                            "B: $background",
                            "A: $assessment",
                            "R: $recommendation",
                        ).joinToString("\n"),
                        wardId = tile.wardId,
                    )
                    sent = true
                }
            }
        },
    ) {
        item {
            ScreenHeaderRow(
                title = "Escalate patient",
                subtitle = "${tile.patientName ?: "Inpatient"} · ${tile.bedLine()}",
                onBack = onBack,
            )
        }

        item {
            if (critical) {
                EscalationBanner(
                    badge = spo2.toString(),
                    badgeCaption = "SpO₂",
                    title = "Rapid response criteria met",
                    body = vitalsLine,
                )
            } else {
                NoticeBanner(title = "Latest observations", text = vitalsLine, tone = Tone.Muted)
            }
        }

        item { SectionHeader("SBAR — drafted for you") }
        item {
            CedarCard {
                SbarField("S", situation) { situation = it }
                Spacer(Modifier.height(10.dp))
                SbarField("B", background) { background = it }
                Spacer(Modifier.height(10.dp))
                SbarField("A", assessment) { assessment = it }
                Spacer(Modifier.height(10.dp))
                SbarField("R", recommendation) { recommendation = it }
            }
        }

        if (sent) {
            item { NoticeBanner(text = "Escalation raised. It is now on the ward's active alert list.", tone = Tone.Ok) }
        }

        item {
            Text(
                "The charge nurse is notified automatically.",
                Modifier.fillMaxWidth(),
                fontSize = 12.sp,
                color = CedarFaint,
                textAlign = androidx.compose.ui.text.style.TextAlign.Center,
            )
        }
    }
}

@Composable
internal fun SbarField(letter: String, value: String, onValueChange: (String) -> Unit) {
    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
        Box(
            Modifier.size(22.dp).background(com.cedarview.hms.core.designsystem.theme.CedarPrimarySoft, RoundedCornerShape(7.dp))
                .padding(top = 0.dp),
            contentAlignment = Alignment.Center,
        ) {
            Text(letter, fontSize = 11.sp, fontWeight = FontWeight.Bold, color = com.cedarview.hms.core.designsystem.theme.CedarPrimaryDark)
        }
        androidx.compose.foundation.text.BasicTextField(
            value = value,
            onValueChange = onValueChange,
            modifier = Modifier.weight(1f)
                .border(1.dp, CedarLine, RoundedCornerShape(10.dp))
                .padding(horizontal = 10.dp, vertical = 9.dp),
            textStyle = MaterialTheme.typography.bodyMedium.copy(color = CedarInk, lineHeight = 19.sp),
        )
    }
}

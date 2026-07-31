package com.cedarview.hms.feature.nurse

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Checklist
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.LocalHospital
import androidx.compose.material.icons.filled.MedicalServices
import androidx.compose.material.icons.filled.MonitorHeart
import androidx.compose.material.icons.automirrored.filled.Notes
import androidx.compose.material.icons.filled.NotificationsNone
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.QrCodeScanner
import androidx.compose.material.icons.filled.SwapHoriz
import androidx.compose.material.icons.filled.WaterDrop
import androidx.compose.material3.Icon
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.AcuityRail
import com.cedarview.hms.core.designsystem.component.AvatarCircle
import com.cedarview.hms.core.designsystem.component.BedCell
import com.cedarview.hms.core.designsystem.component.CedarPage
import com.cedarview.hms.core.designsystem.component.CedarTab
import com.cedarview.hms.core.designsystem.component.Chevron
import com.cedarview.hms.core.designsystem.component.CircleButton
import com.cedarview.hms.core.designsystem.component.EmptyCard
import com.cedarview.hms.core.designsystem.component.GroupedCard
import com.cedarview.hms.core.designsystem.component.GroupedDivider
import com.cedarview.hms.core.designsystem.component.HeroHeader
import com.cedarview.hms.core.designsystem.component.LocalTabNavigator
import com.cedarview.hms.core.designsystem.component.PatientBanner
import com.cedarview.hms.core.designsystem.component.SectionTitle
import com.cedarview.hms.core.designsystem.component.StatTile
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.TabbedShell
import com.cedarview.hms.core.designsystem.component.TimeCell
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.component.toneBase
import com.cedarview.hms.core.designsystem.theme.CedarAvatarInk
import com.cedarview.hms.core.designsystem.theme.CedarAvatarSoft
import com.cedarview.hms.core.designsystem.theme.CedarFaint
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.core.designsystem.theme.CedarWarn
import com.cedarview.hms.data.remote.dto.BedTileDto
import com.cedarview.hms.data.remote.dto.ClinicalJobDto
import com.cedarview.hms.feature.shell.ProfilePane

/**
 * Nurse app (design doc section N). Bottom tabs mirror the shift → monitor →
 * tasks → handover flow; each patient opens a care hub (N2/N3/N6/N7/N8/N11/N12).
 */
@Composable
fun NurseShell(onSignOut: () -> Unit, viewModel: NurseViewModel = hiltViewModel()) {
    val tabs = listOf(
        CedarTab("Shift", Icons.Filled.Home) { ShiftTab(viewModel) },
        CedarTab("Alerts", Icons.Filled.MonitorHeart) { AlertsTab(viewModel) },
        CedarTab("Tasks", Icons.Filled.Checklist) { TasksTab(viewModel) },
        CedarTab("Handover", Icons.Filled.SwapHoriz) { HandoverTab(viewModel) },
        CedarTab("Profile", Icons.Filled.Person) { ProfilePane(viewModel.name, "Nurse", viewModel.roles, onSignOut) },
    )
    TabbedShell(tabs)
}

// ─────────────────────────────────────────────────────────────────────────────
// N1 Shift board → patient care hub
// ─────────────────────────────────────────────────────────────────────────────
@Composable
private fun ShiftTab(vm: NurseViewModel) {
    var selected by remember { mutableStateOf<BedTileDto?>(null) }
    val current = selected
    if (current == null) ShiftBoard(vm, onSelect = { selected = it })
    else NurseCareHub(vm, current, onBack = { selected = null })
}

@Composable
private fun ShiftBoard(vm: NurseViewModel, onSelect: (BedTileDto) -> Unit) {
    LaunchedEffect(Unit) {
        vm.loadBoard()
        vm.loadTasks()
        vm.loadRapid()
    }
    val boardState by vm.board.collectAsStateWithLifecycle()
    val tasksState by vm.tasks.collectAsStateWithLifecycle()
    val rapidState by vm.rapid.collectAsStateWithLifecycle()
    val openTab = LocalTabNavigator.current

    val board = (boardState as? UiState.Success)?.data
    val occupied = board?.bedBoard?.filter { it.admissionId != null }.orEmpty()
    val openTasks = (tasksState as? UiState.Success)?.data?.filter { it.state != "done" }.orEmpty()
    val alerts = (rapidState as? UiState.Success)?.data?.size ?: 0

    // N1 uses a 16dp rhythm between blocks and 10dp between a heading and its card.
    CedarPage(gap = 16.dp) {
        item {
            HeroHeader(
                eyebrow = shiftEyebrow(occupied.firstOrNull()?.wardName),
                title = vm.name,
            ) {
                CircleButton(Icons.Filled.NotificationsNone, "Unread alerts", badge = alerts > 0)
                AvatarCircle(vm.name, container = CedarAvatarSoft, content = CedarAvatarInk)
            }
        }

        item {
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                StatTile(occupied.size.toString(), "Patients", Modifier.weight(1f))
                StatTile(
                    (board?.medsDueNextHour ?: 0).toString(),
                    "Meds due 1 h",
                    Modifier.weight(1f),
                    valueColor = CedarWarn,
                )
                StatTile(
                    (board?.vitalsDue ?: 0).toString(),
                    "Vitals due",
                    Modifier.weight(1f),
                    valueColor = CedarPrimary,
                )
            }
        }

        item {
            BoardSection("My patients") {
                when (val s = boardState) {
                    is UiState.Loading -> EmptyCard("Loading the ward board…")
                    is UiState.Error -> RetryCard(s.message, vm::loadBoard)
                    is UiState.Success ->
                        if (occupied.isEmpty()) {
                            EmptyCard("No occupied beds on this ward.")
                        } else {
                            GroupedCard {
                                occupied.forEachIndexed { i, tile ->
                                    if (i > 0) GroupedDivider()
                                    PatientBedRow(tile) { onSelect(tile) }
                                }
                            }
                        }
                }
            }
        }

        item {
            BoardSection("Next tasks") {
                if (openTasks.isEmpty()) {
                    EmptyCard("Nothing due right now.")
                } else {
                    GroupedCard {
                        openTasks.take(3).forEachIndexed { i, task ->
                            if (i > 0) GroupedDivider()
                            // The soonest task carries the amber "up next" accent.
                            NextTaskRow(task, imminent = i == 0) { openTab("Tasks") }
                        }
                    }
                }
            }
        }
    }
}

/** Heading + card, on the design's tighter 10dp intra-section gap. */
@Composable
private fun BoardSection(title: String, content: @Composable () -> Unit) {
    Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
        SectionTitle(title)
        content()
    }
}

@Composable
private fun PatientBedRow(tile: BedTileDto, onClick: () -> Unit) {
    val tone = acuityTone(tile)
    Row(
        Modifier.fillMaxWidth().clickable(onClick = onClick).padding(horizontal = 15.dp, vertical = 13.dp),
        horizontalArrangement = Arrangement.spacedBy(12.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        BedCell(tile.bedNumber)
        AcuityRail(toneBase(tone))
        Column(Modifier.weight(1f)) {
            Text(
                tile.nameWithDemographics,
                fontSize = 14.sp,
                fontWeight = FontWeight.SemiBold,
                color = CedarInk,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
            )
            // Diagnoses can be long; the design keeps this line to a single row.
            Text(
                bedRowSummary(tile),
                fontSize = 12.sp,
                color = CedarMuted,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
            )
        }
        StatusPill(acuityLabel(tile), tone)
    }
}

@Composable
private fun NextTaskRow(task: ClinicalJobDto, imminent: Boolean, onClick: () -> Unit) {
    Row(
        Modifier.fillMaxWidth().clickable(onClick = onClick).padding(horizontal = 15.dp, vertical = 13.dp),
        horizontalArrangement = Arrangement.spacedBy(12.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        val accent = imminent || task.priority?.lowercase() in listOf("critical", "urgent")
        TimeCell(clockTime(task.dueAt), color = if (accent) CedarWarn else CedarMuted, bold = accent)
        Text(
            task.title ?: "Task",
            modifier = Modifier.weight(1f),
            fontSize = 14.sp,
            fontWeight = FontWeight.Medium,
            color = CedarInk,
        )
        Chevron()
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Patient care hub — the entry point to N2/N3/N6/N7/N8/N9/N11/N12
// ─────────────────────────────────────────────────────────────────────────────
internal sealed interface CareRoute {
    data object Hub : CareRoute
    data object Mar : CareRoute
    data object Verify : CareRoute
    data object Vitals : CareRoute
    data object Fluids : CareRoute
    data object Notes : CareRoute
    data object Checklist : CareRoute
    data object Transfer : CareRoute
    data object Escalate : CareRoute
}

@Composable
private fun NurseCareHub(vm: NurseViewModel, tile: BedTileDto, onBack: () -> Unit) {
    val admissionId = tile.admissionId ?: 0
    LaunchedEffect(admissionId) { vm.loadAdmission(admissionId) }
    var route by remember(admissionId) { mutableStateOf<CareRoute>(CareRoute.Hub) }
    val back = { route = CareRoute.Hub }

    when (route) {
        CareRoute.Hub -> CareHubScreen(vm, tile, onBack) { route = it }
        CareRoute.Mar -> MarScreen(vm, tile, onBack = back, onScan = { route = CareRoute.Verify })
        CareRoute.Verify -> VerifyScreen(vm, tile, onBack = back)
        CareRoute.Vitals -> VitalsScreen(vm, tile, onBack = back, onEscalate = { route = CareRoute.Escalate })
        CareRoute.Fluids -> FluidsScreen(vm, tile, onBack = back)
        CareRoute.Notes -> NursingNoteScreen(vm, tile, onBack = back)
        CareRoute.Checklist -> DischargeChecklistScreen(vm, tile, onBack = back)
        CareRoute.Transfer -> TransferScreen(vm, tile, onBack = back)
        CareRoute.Escalate -> EscalateScreen(vm, tile, onBack = back)
    }
}

@Composable
private fun CareHubScreen(
    vm: NurseViewModel,
    tile: BedTileDto,
    onBack: () -> Unit,
    onOpen: (CareRoute) -> Unit,
) {
    val marState by vm.mar.collectAsStateWithLifecycle()
    val vitalsState by vm.vitals.collectAsStateWithLifecycle()
    val fluidState by vm.fluid.collectAsStateWithLifecycle()
    val notesState by vm.notes.collectAsStateWithLifecycle()
    val checklistState by vm.checklist.collectAsStateWithLifecycle()

    val mar = (marState as? UiState.Success)?.data.orEmpty()
    val due = mar.count { !it.isGiven }
    val vitals = (vitalsState as? UiState.Success)?.data.orEmpty()
    val balance = (fluidState as? UiState.Success)?.data?.firstOrNull()
    val notes = (notesState as? UiState.Success)?.data.orEmpty()
    val checklist = (checklistState as? UiState.Success)?.data
    val checklistDone = checklist?.items?.count { it.done } ?: 0
    val checklistTotal = checklist?.items?.size ?: 0

    CedarPage {
        item {
            ScreenHeaderRow(
                title = tile.patientName ?: "Inpatient",
                subtitle = listOfNotNull(tile.bedNumber?.let { "Bed $it" }, tile.wardName).joinToString(" · "),
                onBack = onBack,
            )
        }

        item {
            PatientBanner(
                name = tile.patientName ?: "Inpatient",
                detail = listOfNotNull(tile.admissionNo, tile.bedType).joinToString(" · ").ifBlank { "Admitted" },
            ) {
                Box(
                    Modifier.size(44.dp).background(CedarPrimary, RoundedCornerShape(13.dp))
                        .clickable { onOpen(CareRoute.Verify) },
                    contentAlignment = Alignment.Center,
                ) {
                    Icon(Icons.Filled.QrCodeScanner, "Verify wristband", tint = Color.White, modifier = Modifier.size(21.dp))
                }
            }
        }

        item {
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                StatTile(due.toString(), "Meds due", Modifier.weight(1f), valueColor = if (due > 0) CedarWarn else CedarInk)
                StatTile(vitals.size.toString(), "Obs today", Modifier.weight(1f))
                StatTile(
                    balance?.balance?.toInt()?.toString() ?: "—",
                    "Balance mL",
                    Modifier.weight(1f),
                    valueColor = CedarPrimary,
                )
            }
        }

        item { SectionTitle("Care") }
        item {
            GroupedCard {
                CareActionRow(
                    Icons.Filled.MedicalServices, Tone.Primary, "Medication round",
                    if (due > 0) "$due due · ${mar.size} scheduled" else "All doses signed",
                ) { onOpen(CareRoute.Mar) }
                GroupedDivider()
                CareActionRow(
                    Icons.Filled.MonitorHeart, Tone.Crit, "Record vitals",
                    vitals.firstOrNull()?.let { "Last ${stampLabel(it.recordedAt)}" } ?: "No observations yet",
                ) { onOpen(CareRoute.Vitals) }
                GroupedDivider()
                CareActionRow(
                    Icons.Filled.WaterDrop, Tone.Primary, "IV & fluid balance",
                    balance?.let { "In ${it.intake.toInt()} · Out ${it.output.toInt()} mL" } ?: "Nothing recorded today",
                ) { onOpen(CareRoute.Fluids) }
                GroupedDivider()
                CareActionRow(
                    Icons.AutoMirrored.Filled.Notes, Tone.Ok, "Nursing note",
                    if (notes.isEmpty()) "No notes filed" else "${notes.size} filed",
                ) { onOpen(CareRoute.Notes) }
            }
        }

        item { SectionTitle("Pathway") }
        item {
            GroupedCard {
                CareActionRow(
                    Icons.Filled.Checklist, Tone.Ok, "Discharge checklist",
                    if (checklistTotal > 0) "$checklistDone of $checklistTotal complete" else "Not started",
                ) { onOpen(CareRoute.Checklist) }
                GroupedDivider()
                CareActionRow(Icons.Filled.SwapHoriz, Tone.Muted, "Transfer patient", "Move to another bed") {
                    onOpen(CareRoute.Transfer)
                }
                GroupedDivider()
                CareActionRow(Icons.Filled.LocalHospital, Tone.Crit, "Escalate", "Raise a rapid response") {
                    onOpen(CareRoute.Escalate)
                }
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Shared row/scaffold pieces used across the nurse screens
// ─────────────────────────────────────────────────────────────────────────────
@Composable
internal fun ScreenHeaderRow(title: String, subtitle: String?, onBack: (() -> Unit)?, trailing: @Composable () -> Unit = {}) {
    com.cedarview.hms.core.designsystem.component.ScreenHeader(
        title = title,
        subtitle = subtitle?.takeIf { it.isNotBlank() },
        onBack = onBack,
    ) { trailing() }
}

@Composable
internal fun CareActionRow(
    icon: ImageVector,
    tone: Tone,
    title: String,
    subtitle: String,
    onClick: () -> Unit,
) {
    Row(
        Modifier.fillMaxWidth().clickable(onClick = onClick).padding(horizontal = 15.dp, vertical = 13.dp),
        horizontalArrangement = Arrangement.spacedBy(12.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Surface(shape = RoundedCornerShape(11.dp), color = toneBase(tone).copy(alpha = 0.12f), modifier = Modifier.size(38.dp)) {
            Box(contentAlignment = Alignment.Center) {
                Icon(icon, contentDescription = null, tint = toneBase(tone), modifier = Modifier.size(19.dp))
            }
        }
        Column(Modifier.weight(1f)) {
            Text(title, fontSize = 14.sp, fontWeight = FontWeight.SemiBold, color = CedarInk)
            Text(subtitle, fontSize = 12.sp, color = CedarMuted)
        }
        Chevron()
    }
}

@Composable
internal fun RetryCard(message: String, onRetry: () -> Unit) {
    Surface(
        Modifier.fillMaxWidth().clickable(onClick = onRetry),
        shape = RoundedCornerShape(16.dp),
        color = com.cedarview.hms.core.designsystem.theme.CedarSurface,
        border = androidx.compose.foundation.BorderStroke(1.dp, com.cedarview.hms.core.designsystem.theme.CedarLine),
    ) {
        Column(Modifier.padding(15.dp)) {
            Text(message, fontSize = 13.sp, color = CedarMuted)
            Text("Tap to retry", fontSize = 13.sp, fontWeight = FontWeight.SemiBold, color = CedarPrimary, modifier = Modifier.padding(top = 6.dp))
        }
    }
}

/** Small legend chip used above lists ("By time", "7 monitored"). */
@Composable
internal fun MetaChip(text: String, tone: Tone = Tone.Muted) {
    Surface(
        shape = RoundedCornerShape(999.dp),
        color = if (tone == Tone.Muted) com.cedarview.hms.core.designsystem.theme.CedarSurface else toneBase(tone).copy(alpha = 0.12f),
        border = if (tone == Tone.Muted) androidx.compose.foundation.BorderStroke(1.dp, com.cedarview.hms.core.designsystem.theme.CedarLine) else null,
    ) {
        Text(
            text,
            fontSize = 12.sp,
            fontWeight = FontWeight.SemiBold,
            color = if (tone == Tone.Muted) CedarMuted else toneBase(tone),
            modifier = Modifier.padding(horizontal = 12.dp, vertical = 7.dp),
        )
    }
}

@Composable
internal fun HistoryRow(time: String, text: String, secondary: String? = null) {
    Row(
        Modifier.fillMaxWidth().padding(horizontal = 15.dp, vertical = 13.dp),
        horizontalArrangement = Arrangement.spacedBy(11.dp),
    ) {
        Text(time, modifier = Modifier.width(62.dp), fontSize = 12.sp, fontWeight = FontWeight.SemiBold, color = CedarMuted)
        Column(Modifier.weight(1f)) {
            Text(text, fontSize = 13.sp, color = CedarInk, lineHeight = 19.sp)
            if (secondary != null) Text(secondary, fontSize = 12.sp, color = CedarFaint)
        }
    }
}

@Composable
internal fun GroupedHeaderRow(text: String) {
    Text(
        text.uppercase(),
        modifier = Modifier.fillMaxWidth().padding(horizontal = 15.dp, vertical = 12.dp),
        fontSize = 11.sp,
        fontWeight = FontWeight.SemiBold,
        letterSpacing = 0.6.sp,
        color = CedarFaint,
    )
}

package com.cedarview.hms.feature.nurse

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.BottomActionBar
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarPage
import com.cedarview.hms.core.designsystem.component.CedarTextField
import com.cedarview.hms.core.designsystem.component.EmptyCard
import com.cedarview.hms.core.designsystem.component.GroupedCard
import com.cedarview.hms.core.designsystem.component.GroupedDivider
import com.cedarview.hms.core.designsystem.component.HeroHeader
import com.cedarview.hms.core.designsystem.component.NoticeBanner
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.component.ProgressCard
import com.cedarview.hms.core.designsystem.component.SbarLine
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.SectionTitle
import com.cedarview.hms.core.designsystem.component.SquareCheck
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.TimeCell
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.theme.CedarCrit
import com.cedarview.hms.core.designsystem.theme.CedarFaint
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarWarn
import com.cedarview.hms.data.remote.dto.ClinicalJobDto
import com.cedarview.hms.data.remote.dto.HandoverDto

// Ward-level nurse screens: N5 task timeline, N4 shift handover and the
// ward alert list that N9 escalations land on.

// ─────────────────────────────────────────────────────────────────────────────
// N5 — Task timeline
// ─────────────────────────────────────────────────────────────────────────────
@Composable
internal fun TasksTab(vm: NurseViewModel) {
    LaunchedEffect(Unit) { vm.loadTasks() }
    val state by vm.tasks.collectAsStateWithLifecycle()
    val tasks = (state as? UiState.Success)?.data.orEmpty()
    val done = tasks.count { it.state == "done" }

    CedarPage {
        item {
            HeroHeader(eyebrow = shiftEyebrow(null), title = "My tasks") {
                MetaChip("By time")
            }
        }

        if (tasks.isNotEmpty()) {
            item { ProgressCard("Shift progress", "$done of ${tasks.size}", done.toFloat() / tasks.size) }
        }

        when (val s = state) {
            is UiState.Loading -> item { EmptyCard("Loading your task list…") }
            is UiState.Error -> item { RetryCard(s.message, vm::loadTasks) }
            is UiState.Success ->
                if (tasks.isEmpty()) {
                    item { EmptyCard("No tasks assigned to nursing right now.") }
                } else {
                    items(tasks.size) { i ->
                        val task = tasks[i]
                        val id = task.id
                        TaskRow(task, onToggle = { if (id != null && task.state != "done") vm.completeTask(id) })
                    }
                }
        }

        item {
            Text(
                "Tap the box to sign a task off. Completed tasks stay on the list for the rest of the shift.",
                Modifier.fillMaxWidth().padding(horizontal = 20.dp),
                fontSize = 12.sp,
                color = CedarFaint,
                lineHeight = 18.sp,
            )
        }
    }
}

@Composable
private fun TaskRow(task: ClinicalJobDto, onToggle: () -> Unit) {
    val complete = task.state == "done"
    val urgent = task.priority?.lowercase() in listOf("critical", "urgent")
    CedarCard(padding = 14.dp) {
        Row(
            Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.spacedBy(13.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            SquareCheck(complete, onToggle = onToggle)
            TimeCell(
                clockTime(task.dueAt),
                color = if (complete) CedarFaint else if (urgent) CedarWarn else CedarMuted,
                bold = urgent && !complete,
            )
            Column(Modifier.weight(1f)) {
                Text(
                    task.title ?: "Task",
                    fontSize = 14.sp,
                    fontWeight = FontWeight.SemiBold,
                    color = if (complete) CedarFaint else CedarInk,
                )
                task.description?.takeIf { it.isNotBlank() }?.let {
                    Text(it, fontSize = 12.sp, color = CedarFaint)
                }
            }
            if (!complete && task.priority != null) {
                StatusPill(task.priority!!.replaceFirstChar { it.uppercase() }, priorityTone(task.priority))
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// N4 — Shift handover (SBAR)
// ─────────────────────────────────────────────────────────────────────────────
@Composable
internal fun HandoverTab(vm: NurseViewModel) {
    LaunchedEffect(Unit) { vm.loadHandovers() }
    val state by vm.handovers.collectAsStateWithLifecycle()
    val handovers = (state as? UiState.Success)?.data.orEmpty()

    var shift by remember { mutableStateOf(currentShiftTransition()) }
    var situation by remember { mutableStateOf("") }
    var background by remember { mutableStateOf("") }
    var assessment by remember { mutableStateOf("") }
    var recommendation by remember { mutableStateOf("") }
    val sbar = listOf("S" to situation, "B" to background, "A" to assessment, "R" to recommendation)
    val canSubmit = sbar.any { it.second.isNotBlank() }

    CedarPage(
        bottomBar = {
            BottomActionBar {
                PrimaryButton("Mark handed over", Modifier.weight(1f), enabled = canSubmit) {
                    vm.addHandover(
                        summary = sbar.filter { it.second.isNotBlank() }.joinToString("\n") { "${it.first}: ${it.second}" },
                        shiftLabel = shift,
                    )
                    situation = ""; background = ""; assessment = ""; recommendation = ""
                }
            }
        },
    ) {
        item {
            HeroHeader(eyebrow = shiftEyebrow(null), title = "Shift handover") {
                MetaChip("${handovers.size} filed")
            }
        }

        item { SectionTitle("New handover") }
        item {
            CedarCard {
                CedarTextField(shift, { shift = it }, "Shift")
                Spacer(Modifier.height(12.dp))
                SectionHeader("SBAR")
                Spacer(Modifier.height(10.dp))
                SbarField("S", situation) { situation = it }
                Spacer(Modifier.height(10.dp))
                SbarField("B", background) { background = it }
                Spacer(Modifier.height(10.dp))
                SbarField("A", assessment) { assessment = it }
                Spacer(Modifier.height(10.dp))
                SbarField("R", recommendation) { recommendation = it }
            }
        }

        item { SectionTitle("Recent") }
        when (val s = state) {
            is UiState.Loading -> item { EmptyCard("Loading recent handovers…") }
            is UiState.Error -> item { RetryCard(s.message, vm::loadHandovers) }
            is UiState.Success ->
                if (handovers.isEmpty()) {
                    item { EmptyCard("No handovers filed for this ward yet.") }
                } else {
                    items(handovers.size) { i -> HandoverCard(handovers[i]) }
                }
        }
    }
}

@Composable
private fun HandoverCard(handover: HandoverDto) {
    val sbar = parseSbar(handover.summary)
    CedarCard {
        Row(Modifier.fillMaxWidth(), verticalAlignment = Alignment.CenterVertically) {
            Column(Modifier.weight(1f)) {
                Text(handover.shiftLabel ?: "Handover", fontSize = 15.sp, fontWeight = FontWeight.SemiBold, color = CedarInk)
                Text(stampLabel(handover.handedOverAt), fontSize = 12.sp, color = CedarMuted)
            }
            StatusPill(
                handover.state?.replace('_', ' ')?.replaceFirstChar { it.uppercase() } ?: "Submitted",
                if (handover.state == "accepted") Tone.Ok else Tone.Primary,
            )
        }
        Spacer(Modifier.height(13.dp))
        if (sbar.isEmpty()) {
            Text(handover.summary ?: "—", fontSize = 13.sp, color = CedarInk, lineHeight = 19.sp)
        } else {
            sbar.forEachIndexed { i, (letter, text) ->
                if (i > 0) Spacer(Modifier.height(9.dp))
                SbarLine(letter, text)
            }
        }
    }
}

private val SBAR_LINE = Regex("^([SBAR])\\s*:\\s*(.*)$")

/** Splits a stored handover summary back into its S/B/A/R paragraphs. */
private fun parseSbar(summary: String?): List<Pair<String, String>> =
    summary?.lines().orEmpty().mapNotNull { line ->
        SBAR_LINE.find(line.trim())?.let { it.groupValues[1] to it.groupValues[2] }
    }.filter { it.second.isNotBlank() }

// ─────────────────────────────────────────────────────────────────────────────
// Ward alerts — where N9 escalations land
// ─────────────────────────────────────────────────────────────────────────────
@Composable
internal fun AlertsTab(vm: NurseViewModel) {
    LaunchedEffect(Unit) { vm.loadRapid() }
    val state by vm.rapid.collectAsStateWithLifecycle()
    val events = (state as? UiState.Success)?.data.orEmpty()

    var location by remember { mutableStateOf("") }
    var reason by remember { mutableStateOf("") }

    CedarPage(
        bottomBar = {
            BottomActionBar {
                PrimaryButton(
                    "Raise rapid response",
                    Modifier.weight(1f),
                    enabled = location.isNotBlank() || reason.isNotBlank(),
                    container = CedarCrit,
                ) {
                    vm.raiseRapid(location, reason)
                    location = ""; reason = ""
                }
            }
        },
    ) {
        item {
            HeroHeader(eyebrow = "Live · ward escalations", title = "Alerts") {
                MetaChip(
                    if (events.isEmpty()) "All clear" else "${events.size} active",
                    if (events.isEmpty()) Tone.Ok else Tone.Crit,
                )
            }
        }

        item { SectionTitle("Active") }
        when (val s = state) {
            is UiState.Loading -> item { EmptyCard("Checking for active events…") }
            is UiState.Error -> item { RetryCard(s.message, vm::loadRapid) }
            is UiState.Success ->
                if (events.isEmpty()) {
                    item {
                        NoticeBanner(
                            title = "No active escalations",
                            text = "Rapid responses raised from a patient's record appear here for the whole ward.",
                            tone = Tone.Ok,
                        )
                    }
                } else {
                    item {
                        GroupedCard(borderWidth = 1.5.dp, border = CedarCrit) {
                            events.forEachIndexed { i, event ->
                                if (i > 0) GroupedDivider()
                                Row(
                                    Modifier.fillMaxWidth().padding(horizontal = 15.dp, vertical = 13.dp),
                                    horizontalArrangement = Arrangement.spacedBy(12.dp),
                                    verticalAlignment = Alignment.CenterVertically,
                                ) {
                                    Column(Modifier.weight(1f)) {
                                        Text(
                                            event.patient?.name ?: event.location ?: "Rapid response",
                                            fontSize = 15.sp,
                                            fontWeight = FontWeight.SemiBold,
                                            color = CedarInk,
                                        )
                                        Text(
                                            listOfNotNull(
                                                event.ward?.name,
                                                event.location.takeIf { it != null && event.patient != null },
                                                stampLabel(event.raisedAt),
                                            ).joinToString(" · "),
                                            fontSize = 12.sp,
                                            color = CedarMuted,
                                        )
                                        event.reason?.takeIf { it.isNotBlank() }?.let {
                                            Text(it, fontSize = 12.sp, color = CedarFaint, lineHeight = 18.sp, modifier = Modifier.padding(top = 4.dp))
                                        }
                                    }
                                    StatusPill(
                                        (event.state ?: "active").replaceFirstChar { it.uppercase() },
                                        Tone.Crit,
                                        solid = event.state == "active",
                                    )
                                }
                            }
                        }
                    }
                }
        }

        item { SectionTitle("Raise an alert") }
        item {
            CedarCard {
                CedarTextField(location, { location = it }, "Location (ward · bed)")
                Spacer(Modifier.height(8.dp))
                CedarTextField(reason, { reason = it }, "What is happening?", singleLine = false, minLines = 3)
            }
        }
        item {
            NoticeBanner(
                text = "For a specific inpatient, escalate from their record so the SBAR and latest observations travel with the alert.",
                tone = Tone.Warn,
            )
        }
    }
}

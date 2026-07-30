package com.cedarview.hms.feature.doctor.patient

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
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.CedarTextField
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.data.remote.dto.RxLineRequest
import com.cedarview.hms.feature.doctor.DoctorAppViewModel

/**
 * D4 — Prescription authoring for a single OPD visit. The doctor taps a recent
 * drug (or types one), sets dose/frequency/duration and adds it to the list, then
 * saves the whole set to the visit via POST /doctor/visits/{visitId}/prescription.
 */
@Composable
fun PrescriptionScreen(
    visitId: Int,
    vm: DoctorAppViewModel,
    onBack: () -> Unit,
    onSaved: () -> Unit,
) {
    LaunchedEffect(Unit) { vm.loadRecentDrugs() }
    val recent by vm.recentDrugs.collectAsStateWithLifecycle()
    val lines = remember { mutableStateListOf<RxLineRequest>() }

    var drug by remember { mutableStateOf("") }
    var dose by remember { mutableStateOf("") }
    var frequency by remember { mutableStateOf("") }
    var duration by remember { mutableStateOf("") }
    var advice by remember { mutableStateOf("") }
    var saving by remember { mutableStateOf(false) }

    CedarScaffold(title = "Prescription", onBack = onBack) { padding ->
        LazyColumn(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            // Recent drugs quick-pick
            item { SectionHeader("Recent drugs") }
            when (val s = recent) {
                is UiState.Success ->
                    if (s.data.drugs.isEmpty()) item { CedarCard { Text("No recent drugs.", color = CedarMuted) } }
                    else items(s.data.drugs, key = { "rd" + (it.drugId ?: it.hashCode()) }) { d ->
                        CedarCard(Modifier.clickable { drug = d.label }) {
                            Text(d.label, color = CedarInk)
                            d.genericName?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                        }
                    }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
            }

            // Composer
            item {
                CedarCard {
                    SectionHeader("Add drug")
                    CedarTextField(drug, { drug = it }, "Drug name")
                    Spacer(Modifier.height(8.dp))
                    Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                        CedarTextField(dose, { dose = it }, "Dose", modifier = Modifier.weight(1f))
                        CedarTextField(frequency, { frequency = it }, "Frequency", modifier = Modifier.weight(1f))
                    }
                    Spacer(Modifier.height(8.dp))
                    CedarTextField(duration, { duration = it }, "Duration")
                    Spacer(Modifier.height(12.dp))
                    PrimaryButton("Add to list", enabled = drug.isNotBlank()) {
                        lines.add(
                            RxLineRequest(
                                drugName = drug.trim(),
                                dose = dose.ifBlank { null },
                                frequency = frequency.ifBlank { null },
                                duration = duration.ifBlank { null },
                            ),
                        )
                        drug = ""; dose = ""; frequency = ""; duration = ""
                    }
                }
            }

            // Pending lines
            if (lines.isNotEmpty()) {
                item { SectionHeader("On this prescription (${lines.size})") }
                itemsIndexed(lines) { index, line ->
                    CedarCard {
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                            Column(Modifier.weight(1f)) {
                                Text(line.drugName ?: "Drug", color = CedarInk)
                                Text(listOfNotNull(line.dose, line.frequency, line.duration).joinToString(" · "), style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                            }
                            TextButton(onClick = { lines.removeAt(index) }) { Text("Remove") }
                        }
                    }
                }
                item {
                    CedarCard {
                        CedarTextField(advice, { advice = it }, "Advice / notes", singleLine = false)
                    }
                }
                item {
                    Spacer(Modifier.height(4.dp))
                    PrimaryButton("Save prescription", loading = saving) {
                        saving = true
                        vm.savePrescription(visitId, advice, lines.toList()) {
                            saving = false
                            onSaved()
                        }
                    }
                }
            } else {
                item { StatusPill("Add at least one drug", Tone.Muted) }
            }
            item { Spacer(Modifier.height(24.dp)) }
        }
    }
}

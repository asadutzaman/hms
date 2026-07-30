package com.cedarview.hms.feature.doctor.patient

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
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.Avatar
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.InfoRow
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.data.remote.dto.DoctorApptDto
import com.cedarview.hms.feature.doctor.DoctorAppViewModel

/**
 * D2 — Doctor patient profile. Composes the patient card, allergies, most-recent
 * prescription (copy-forward reference), open lab orders and visit history from
 * the five per-patient endpoints. "Prescribe" opens D4 for the visit tied to the
 * appointment (enabled only when an OPD visit exists).
 */
@Composable
fun PatientProfileScreen(
    appt: DoctorApptDto,
    vm: DoctorAppViewModel,
    onBack: () -> Unit,
    onPrescribe: (visitId: Int) -> Unit,
) {
    val patientId = appt.patientId ?: 0
    LaunchedEffect(patientId) { vm.loadPatient(patientId) }
    val profile by vm.profile.collectAsStateWithLifecycle()
    val allergies by vm.allergies.collectAsStateWithLifecycle()
    val latestRx by vm.latestRx.collectAsStateWithLifecycle()
    val labs by vm.patientLabs.collectAsStateWithLifecycle()
    val history by vm.history.collectAsStateWithLifecycle()

    CedarScaffold(title = appt.patientName ?: "Patient", onBack = onBack) { padding ->
        LazyColumn(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            // Header card
            item {
                CedarCard {
                    Row {
                        Avatar((profile as? UiState.Success)?.data?.displayName ?: appt.patientName)
                        Column(Modifier.padding(start = 12.dp)) {
                            val p = (profile as? UiState.Success)?.data
                            Text(p?.displayName ?: appt.patientName ?: "Patient", style = MaterialTheme.typography.titleMedium, color = CedarInk, fontWeight = FontWeight.Bold)
                            Text(listOfNotNull(p?.mrn, p?.gender, p?.bloodGroup).joinToString(" · ").ifBlank { "—" }, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                        }
                    }
                    (profile as? UiState.Success)?.data?.let { p ->
                        Spacer(Modifier.height(8.dp))
                        InfoRow("Phone", p.primaryPhone)
                        InfoRow("Email", p.email)
                    }
                }
            }

            item {
                PrimaryButton(
                    "Prescribe",
                    enabled = appt.opdVisitId != null,
                ) { appt.opdVisitId?.let(onPrescribe) }
                if (appt.opdVisitId == null) {
                    Text("Prescribing needs an open OPD visit for this appointment.", style = MaterialTheme.typography.bodySmall, color = CedarMuted, modifier = Modifier.padding(top = 4.dp))
                }
            }

            // Allergies
            item { SectionHeader("Allergies") }
            when (val s = allergies) {
                is UiState.Success ->
                    if (s.data.isEmpty()) item { CedarCard { Text("No known allergies.", color = CedarMuted) } }
                    else items(s.data, key = { "al" + (it.id ?: it.hashCode()) }) { a ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text(a.allergen ?: "Allergen", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                a.severity?.let { StatusPill(it, Tone.Warn) }
                            }
                            a.reaction?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                        }
                    }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
            }

            // Latest prescription
            item { SectionHeader("Most recent prescription") }
            when (val s = latestRx) {
                is UiState.Success -> {
                    val items = s.data.items
                    if (items.isEmpty()) item { CedarCard { Text("No prior prescription.", color = CedarMuted) } }
                    else item {
                        CedarCard {
                            s.data.prescription?.prescribedAt?.let { Text(it, style = MaterialTheme.typography.labelSmall, color = CedarMuted) }
                            items.forEach { rx ->
                                Text("• ${rx.drugName ?: rx.genericName ?: "Drug"}", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                Text(listOfNotNull(rx.dose, rx.frequency, rx.duration).joinToString(" · "), style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                            }
                        }
                    }
                }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
            }

            // Lab orders
            item { SectionHeader("Lab orders") }
            when (val s = labs) {
                is UiState.Success ->
                    if (s.data.isEmpty()) item { CedarCard { Text("No lab orders.", color = CedarMuted) } }
                    else items(s.data, key = { "lo" + (it.id ?: it.hashCode()) }) { o ->
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
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
            }

            // Visit history
            item { SectionHeader("Visit history") }
            when (val s = history) {
                is UiState.Success -> {
                    val visits = s.data.visits
                    if (visits.isEmpty()) item { CedarCard { Text("No prior visits.", color = CedarMuted) } }
                    else items(visits, key = { "v" + (it.id ?: it.hashCode()) }) { v ->
                        CedarCard {
                            Text(v.visitNo ?: "Visit", color = CedarInk, fontWeight = FontWeight.SemiBold)
                            Text(listOfNotNull(v.visitDate, v.chiefComplaint).joinToString(" · ").ifBlank { "—" }, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                        }
                    }
                }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
            }
            item { Spacer(Modifier.height(24.dp)) }
        }
    }
}

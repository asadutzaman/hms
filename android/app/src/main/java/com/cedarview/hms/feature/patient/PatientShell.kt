package com.cedarview.hms.feature.patient

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
import androidx.compose.material.icons.filled.CalendarMonth
import androidx.compose.material.icons.filled.CreditCard
import androidx.compose.material.icons.filled.Description
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
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
import com.cedarview.hms.core.designsystem.component.EmptyView
import com.cedarview.hms.core.designsystem.component.ErrorView
import com.cedarview.hms.core.designsystem.component.LoadingView
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.TabbedShell
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.feature.patient.home.PatientHomeScreen

@Composable
fun PatientShell(
    onSignOut: () -> Unit,
    onFindDoctor: () -> Unit,
    viewModel: PatientDataViewModel = hiltViewModel(),
) {
    val tabs = listOf(
        CedarTab("Home", Icons.Filled.Home) { PatientHomeScreen(onFindDoctor = onFindDoctor, onSignOut = onSignOut) },
        CedarTab("Visits", Icons.Filled.CalendarMonth) { VisitsTab(viewModel) },
        CedarTab("Records", Icons.Filled.Description) { RecordsTab(viewModel) },
        CedarTab("Pay", Icons.Filled.CreditCard) { PayTab(viewModel) },
        CedarTab("Profile", Icons.Filled.Person) { ProfileTab(viewModel.patientName, onSignOut) },
    )
    TabbedShell(tabs)
}

@Composable
private fun VisitsTab(vm: PatientDataViewModel) {
    LaunchedEffect(Unit) { vm.loadAppointments() }
    val state by vm.appointments.collectAsStateWithLifecycle()
    CedarScaffold(title = "My visits") { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = vm::loadAppointments)
            is UiState.Success -> {
                if (s.data.isEmpty()) EmptyView("No appointments yet.")
                else LazyColumn(
                    Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp),
                ) {
                    items(s.data, key = { it.id ?: it.hashCode() }) { appt ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Column(Modifier.weight(1f)) {
                                    Text(appt.reasonForVisit ?: "Consultation", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                                    Text(
                                        listOfNotNull(appt.appointmentDate, appt.appointmentTime).joinToString(" · ").ifBlank { "Scheduled" },
                                        style = MaterialTheme.typography.bodyMedium, color = CedarMuted,
                                    )
                                }
                                StatusPill(appt.status ?: "booked", toneFor(appt.status))
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun RecordsTab(vm: PatientDataViewModel) {
    LaunchedEffect(Unit) { vm.loadPrescriptions(); vm.loadLabReports() }
    val rx by vm.prescriptions.collectAsStateWithLifecycle()
    val labs by vm.labReports.collectAsStateWithLifecycle()
    CedarScaffold(title = "Records") { padding ->
        LazyColumn(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
            verticalArrangement = Arrangement.spacedBy(8.dp),
        ) {
            item { SectionHeader("Prescriptions") }
            when (val s = rx) {
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Success ->
                    if (s.data.isEmpty()) item { CedarCard { Text("No prescriptions.", color = CedarMuted) } }
                    else items(s.data, key = { "rx" + (it.id ?: it.hashCode()) }) { p ->
                        CedarCard {
                            Text("Prescription", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                            Text(
                                listOfNotNull(p.prescribedAt, p.itemCount?.let { "$it item(s)" }).joinToString(" · "),
                                style = MaterialTheme.typography.bodyMedium, color = CedarMuted,
                            )
                            p.advice?.takeIf { it.isNotBlank() }?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                        }
                    }
            }
            item { Spacer(Modifier.height(6.dp)); SectionHeader("Lab reports") }
            when (val s = labs) {
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Success ->
                    if (s.data.isEmpty()) item { CedarCard { Text("No lab reports released.", color = CedarMuted) } }
                    else items(s.data, key = { "lab" + (it.id ?: it.hashCode()) }) { r ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text(r.orderNo ?: "Lab report", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                                StatusPill(r.status ?: "—", Tone.Muted)
                            }
                        }
                    }
            }
        }
    }
}

@Composable
private fun PayTab(vm: PatientDataViewModel) {
    LaunchedEffect(Unit) { vm.loadBills() }
    val state by vm.bills.collectAsStateWithLifecycle()
    CedarScaffold(title = "Billing") { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = vm::loadBills)
            is UiState.Success -> {
                val all = s.data.opd + s.data.ipd
                if (all.isEmpty()) EmptyView("No bills.")
                else LazyColumn(
                    Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp),
                ) {
                    items(all, key = { it.id ?: it.hashCode() }) { bill ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Column(Modifier.weight(1f)) {
                                    Text(bill.billNo ?: "Invoice", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                                    Text("Total $" + "%.2f".format(bill.netAmount ?: 0.0), style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                                }
                                val due = bill.dueAmount ?: 0.0
                                StatusPill(if (due > 0) "Due $" + "%.0f".format(due) else "Paid", if (due > 0) Tone.Warn else Tone.Ok)
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun ProfileTab(name: String, onSignOut: () -> Unit) {
    CedarScaffold(title = "Profile") { padding ->
        Column(Modifier.fillMaxSize().padding(padding).padding(18.dp)) {
            CedarCard {
                Row {
                    Avatar(name)
                    Column(Modifier.padding(start = 12.dp)) {
                        Text(name, style = MaterialTheme.typography.titleMedium, color = CedarInk, fontWeight = FontWeight.Bold)
                        Text("Patient", style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                    }
                }
            }
            Spacer(Modifier.height(16.dp))
            PrimaryButton("Sign out", onClick = onSignOut)
        }
    }
}

private fun toneFor(status: String?): Tone = when (status?.lowercase()) {
    "completed" -> Tone.Ok
    "checked_in", "in_consultation", "confirmed" -> Tone.Primary
    "cancelled", "no_show" -> Tone.Crit
    "pending" -> Tone.Warn
    else -> Tone.Muted
}

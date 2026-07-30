package com.cedarview.hms.feature.admin

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
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
import androidx.compose.material.icons.filled.Dashboard
import androidx.compose.material.icons.filled.LocalHospital
import androidx.compose.material.icons.filled.MonitorHeart
import androidx.compose.material.icons.filled.MoreHoriz
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
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
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.CedarTab
import com.cedarview.hms.core.designsystem.component.ErrorView
import com.cedarview.hms.core.designsystem.component.LoadingView
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.TabbedShell
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.component.uiStateItems
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarLine
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.core.designsystem.theme.CedarPrimarySoft
import com.cedarview.hms.core.designsystem.theme.CedarSurface
import com.cedarview.hms.feature.shell.ProfilePane

@Composable
fun AdminShell(onSignOut: () -> Unit, viewModel: AdminAppViewModel = hiltViewModel()) {
    val tabs = listOf(
        CedarTab("Overview", Icons.Filled.Dashboard) { OverviewTab(viewModel) },
        CedarTab("Beds", Icons.Filled.Bed) { BedsTab(viewModel) },
        CedarTab("Live", Icons.Filled.MonitorHeart) { LiveTab(viewModel) },
        CedarTab("Monitors", Icons.Filled.LocalHospital) { MonitorsTab(viewModel) },
        CedarTab("More", Icons.Filled.MoreHoriz) { MoreTab(viewModel, onSignOut) },
    )
    TabbedShell(tabs)
}

// ── A1 dashboard + A4 quality/safety + A5 finance ─────────────────────────────
@Composable
private fun OverviewTab(vm: AdminAppViewModel) {
    LaunchedEffect(Unit) { vm.loadDashboard() }
    val state by vm.dashboard.collectAsStateWithLifecycle()
    CedarScaffold(title = "Operations") { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = vm::loadDashboard)
            is UiState.Success -> {
                val h = s.data.hospital
                val k = s.data.mis?.kpis
                val bed = h?.bedOccupancy?.summary
                LazyColumn(
                    Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp),
                ) {
                    item {
                        Row(Modifier.fillMaxWidth().padding(top = 8.dp), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                            Stat("Appointments", (h?.appointmentsToday?.totalCount ?: 0).toString(), Modifier.weight(1f))
                            Stat("Beds occupied", "${bed?.occupied ?: 0}/${bed?.total ?: 0}", Modifier.weight(1f))
                        }
                    }
                    item {
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                            Stat("OPD today", (k?.opdVisitCount ?: 0).toString(), Modifier.weight(1f))
                            Stat("IPD admits", (k?.ipdAdmissionCount ?: 0).toString(), Modifier.weight(1f))
                        }
                    }
                    item { SectionHeader("Finance (A5)") }
                    item {
                        CedarCard {
                            MoneyRow("OPD revenue", k?.opdRevenue)
                            MoneyRow("IPD revenue", k?.ipdRevenue)
                            MoneyRow("Total revenue", k?.totalRevenue)
                        }
                    }
                    item { SectionHeader("Quality & safety (A4)") }
                    item {
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text("Pending lab orders", color = CedarMuted)
                                Text((h?.pendingLabOrders?.pendingCount ?: 0).toString(), color = CedarInk, fontWeight = FontWeight.Bold)
                            }
                        }
                    }
                    val lowStock = h?.lowStockAlerts.orEmpty()
                    if (lowStock.isNotEmpty()) {
                        item { SectionHeader("Low-stock alerts") }
                        items(lowStock) { a ->
                            CedarCard {
                                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                    Text(a.itemInfo?.nameEn ?: "Item", color = CedarInk)
                                    StatusPill("Low stock", Tone.Warn)
                                }
                            }
                        }
                    }
                    item { Spacer(Modifier.height(24.dp)) }
                }
            }
        }
    }
}

// ── A2 Bed occupancy ──────────────────────────────────────────────────────────
@Composable
private fun BedsTab(vm: AdminAppViewModel) {
    LaunchedEffect(Unit) { vm.loadBeds() }
    val state by vm.beds.collectAsStateWithLifecycle()
    CedarScaffold(title = "Bed occupancy") { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = vm::loadBeds)
            is UiState.Success -> {
                val sum = s.data.summary?.summary
                val wards = s.data.summary?.wards.orEmpty()
                LazyColumn(
                    Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp),
                ) {
                    item {
                        Row(Modifier.fillMaxWidth().padding(top = 8.dp), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                            Stat("Occupied", (sum?.occupied ?: 0).toString(), Modifier.weight(1f))
                            Stat("Vacant", (sum?.vacant ?: 0).toString(), Modifier.weight(1f))
                            Stat("Total", (sum?.total ?: 0).toString(), Modifier.weight(1f))
                        }
                    }
                    item { SectionHeader("By ward") }
                    items(wards, key = { it.wardId ?: it.hashCode() }) { w ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text(w.wardName ?: "Ward", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                Text("${w.occupied}/${w.total} occupied", style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                            }
                        }
                    }
                    item { Spacer(Modifier.height(24.dp)) }
                }
            }
        }
    }
}

// ── A3 Live operations ────────────────────────────────────────────────────────
@Composable
private fun LiveTab(vm: AdminAppViewModel) {
    LaunchedEffect(Unit) { vm.loadLiveOps() }
    val state by vm.liveOps.collectAsStateWithLifecycle()
    CedarScaffold(title = "Live operations") { padding ->
        when (val s = state) {
            is UiState.Loading -> LoadingView(Modifier.padding(padding))
            is UiState.Error -> ErrorView(s.message, Modifier.padding(padding), onRetry = vm::loadLiveOps)
            is UiState.Success -> {
                val ed = s.data.edBoard
                val occupied = s.data.bedBoard.count { it.admissionId != null }
                LazyColumn(
                    Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp),
                ) {
                    item {
                        Row(Modifier.fillMaxWidth().padding(top = 8.dp), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                            Stat("In ED", ed.size.toString(), Modifier.weight(1f))
                            Stat("Beds in use", occupied.toString(), Modifier.weight(1f))
                        }
                    }
                    item { SectionHeader("Emergency department") }
                    if (ed.isEmpty()) item { CedarCard { Text("No active ED visits.", color = CedarMuted) } }
                    else items(ed, key = { "ed" + (it.id ?: it.hashCode()) }) { e ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Text(e.patientName ?: (e.erVisitNo ?: "ED visit"), color = CedarInk, fontWeight = FontWeight.SemiBold)
                                StatusPill(e.disposition ?: e.erStatusLabel ?: "in ED", Tone.Warn)
                            }
                        }
                    }
                    item { Spacer(Modifier.height(24.dp)) }
                }
            }
        }
    }
}

// ── A8/A9/A10 Monitors (segmented) ────────────────────────────────────────────
@Composable
private fun MonitorsTab(vm: AdminAppViewModel) {
    var seg by remember { mutableStateOf("opd") }
    LaunchedEffect(seg) {
        when (seg) {
            "opd" -> vm.loadOpd()
            "ipd" -> vm.loadIpd()
            "ed" -> vm.loadEmergency()
        }
    }
    CedarScaffold(title = "Monitors") { padding ->
        Column(Modifier.fillMaxSize().padding(padding)) {
            Row(Modifier.fillMaxWidth().padding(horizontal = 18.dp, vertical = 8.dp), horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                Seg("OPD", seg == "opd", Modifier.weight(1f)) { seg = "opd" }
                Seg("IPD", seg == "ipd", Modifier.weight(1f)) { seg = "ipd" }
                Seg("Emergency", seg == "ed", Modifier.weight(1f)) { seg = "ed" }
            }
            Box(Modifier.weight(1f)) {
                when (seg) {
                    "opd" -> OpdMonitorList(vm)
                    "ipd" -> IpdMonitorList(vm)
                    else -> EdMonitorList(vm)
                }
            }
        }
    }
}

@Composable
private fun OpdMonitorList(vm: AdminAppViewModel) {
    val state by vm.opd.collectAsStateWithLifecycle()
    LazyColumn(Modifier.fillMaxSize().padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
        when (val s = state) {
            is UiState.Success -> {
                item { CedarCard { Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) { Text("OPD visits today", color = CedarMuted); Text(s.data.count.toString(), color = CedarPrimary, fontWeight = FontWeight.Bold) } } }
                items(s.data.visits, key = { "opd" + (it.id ?: it.hashCode()) }) { v ->
                    CedarCard {
                        Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                            Column {
                                Text(v.patient?.name ?: "Patient", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                v.tokenNumber?.let { Text("Token $it", style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                            }
                            StatusPill(v.status ?: "waiting", Tone.Primary)
                        }
                    }
                }
            }
            is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
            is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
        }
    }
}

@Composable
private fun IpdMonitorList(vm: AdminAppViewModel) {
    val state by vm.ipd.collectAsStateWithLifecycle()
    LazyColumn(Modifier.fillMaxSize().padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
        uiStateItems(state, vm::loadIpd, "No admitted patients.") { row ->
            CedarCard {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                    Column {
                        Text(row.patient?.name ?: "Patient", color = CedarInk, fontWeight = FontWeight.SemiBold)
                        Text(listOfNotNull(row.ward?.name, row.bed?.bedNumber).joinToString(" · ").ifBlank { "Ward" }, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                    }
                    Text(row.patient?.mrn ?: "", style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                }
            }
        }
    }
}

@Composable
private fun EdMonitorList(vm: AdminAppViewModel) {
    val state by vm.emergency.collectAsStateWithLifecycle()
    LazyColumn(Modifier.fillMaxSize().padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
        uiStateItems(state, vm::loadEmergency, "No active ED visits.") { e ->
            CedarCard {
                Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                    Column(Modifier.weight(1f)) {
                        Text(e.patientName ?: (e.erVisitNo ?: "ED visit"), color = CedarInk, fontWeight = FontWeight.SemiBold)
                        e.chiefComplaint?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                    }
                    StatusPill(e.disposition ?: e.erStatusLabel ?: "in ED", Tone.Warn)
                }
            }
        }
    }
}

// ── A6 staffing + A7 reports + profile ────────────────────────────────────────
@Composable
private fun MoreTab(vm: AdminAppViewModel, onSignOut: () -> Unit) {
    var section by remember { mutableStateOf<String?>(null) }
    when (section) {
        "staffing" -> StaffingScreen(vm) { section = null }
        "reports" -> ReportsScreen(vm) { section = null }
        "profile" -> ProfilePane(vm.name, "Administrator", vm.roles, onSignOut, onBack = { section = null })
        else -> CedarScaffold(title = "More") { p ->
            LazyColumn(Modifier.fillMaxSize().padding(p).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
                item { MoreRow("Staffing & capacity", "Leave requests and occupancy") { section = "staffing" } }
                item { MoreRow("Reports library", "Collections, occupancy, productivity") { section = "reports" } }
                item { MoreRow("Profile", "Session and sign out") { section = "profile" } }
            }
        }
    }
}

@Composable
private fun StaffingScreen(vm: AdminAppViewModel, onBack: () -> Unit) {
    LaunchedEffect(Unit) { vm.loadStaffing() }
    val state by vm.staffing.collectAsStateWithLifecycle()
    CedarScaffold(title = "Staffing & capacity", onBack = onBack) { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            item { SectionHeader("Recent leave requests") }
            when (val s = state) {
                is UiState.Success ->
                    if (s.data.recentLeaveRequests.isEmpty()) item { CedarCard { Text("No recent leave requests.", color = CedarMuted) } }
                    else items(s.data.recentLeaveRequests, key = { "lv" + (it.id ?: it.hashCode()) }) { l ->
                        CedarCard {
                            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                                Column(Modifier.weight(1f)) {
                                    Text(l.leaveType ?: "Leave", color = CedarInk, fontWeight = FontWeight.SemiBold)
                                    Text(listOfNotNull(l.fromDate, l.toDate).joinToString(" → ").ifBlank { "—" }, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                                }
                                l.state?.let { StatusPill(it, Tone.Muted) }
                            }
                        }
                    }
                is UiState.Error -> item { CedarCard { Text(s.message, color = CedarMuted) } }
                is UiState.Loading -> item { CedarCard { Text("Loading…", color = CedarMuted) } }
            }
        }
    }
}

@Composable
private fun ReportsScreen(vm: AdminAppViewModel, onBack: () -> Unit) {
    LaunchedEffect(Unit) { vm.loadReports() }
    val state by vm.reports.collectAsStateWithLifecycle()
    CedarScaffold(title = "Reports library", onBack = onBack) { padding ->
        LazyColumn(Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp), verticalArrangement = Arrangement.spacedBy(10.dp)) {
            uiStateItems(state, vm::loadReports, "No reports available.") { r ->
                CedarCard {
                    Text(r.title ?: "Report", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                    r.key?.let { Text(it, style = MaterialTheme.typography.bodyMedium, color = CedarMuted) }
                }
            }
        }
    }
}

// ── small shared pieces ───────────────────────────────────────────────────────
@Composable
private fun Stat(label: String, value: String, modifier: Modifier = Modifier) {
    CedarCard(modifier) {
        Text(value, style = MaterialTheme.typography.headlineMedium, color = CedarPrimary, fontWeight = FontWeight.Bold)
        Text(label, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
    }
}

@Composable
private fun MoneyRow(label: String, amount: Double?) {
    Row(Modifier.fillMaxWidth().padding(vertical = 4.dp), horizontalArrangement = Arrangement.SpaceBetween) {
        Text(label, color = CedarMuted)
        Text("$" + "%,.0f".format(amount ?: 0.0), color = CedarInk, fontWeight = FontWeight.SemiBold)
    }
}

@Composable
private fun Seg(label: String, selected: Boolean, modifier: Modifier = Modifier, onClick: () -> Unit) {
    Surface(
        modifier = modifier.height(38.dp).clickable(onClick = onClick),
        shape = androidx.compose.foundation.shape.RoundedCornerShape(10.dp),
        color = if (selected) CedarPrimarySoft else CedarSurface,
        border = androidx.compose.foundation.BorderStroke(1.dp, if (selected) CedarPrimary else CedarLine),
    ) {
        Row(Modifier.fillMaxSize(), horizontalArrangement = Arrangement.Center, verticalAlignment = androidx.compose.ui.Alignment.CenterVertically) {
            Text(label, style = MaterialTheme.typography.labelLarge, color = if (selected) CedarPrimary else CedarMuted)
        }
    }
}

@Composable
private fun MoreRow(title: String, subtitle: String, onClick: () -> Unit) {
    CedarCard(Modifier.clickable(onClick = onClick)) {
        Text(title, style = MaterialTheme.typography.titleMedium, color = CedarInk)
        Text(subtitle, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
    }
}

package com.cedarview.hms.feature.shell

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarLine
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarSurface

/** Staff "choose session" launcher — role-gated entry to each role app. */
@Composable
fun LauncherScreen(
    onOpenDoctor: () -> Unit,
    onOpenWard: () -> Unit,
    onOpenNurse: () -> Unit,
    onOpenOnCall: () -> Unit,
    onOpenAdmin: () -> Unit,
    viewModel: AppSessionViewModel = hiltViewModel(),
) {
    val session by viewModel.session.collectAsStateWithLifecycle()
    val isDoctor = session.hasRole("Doctor")
    // Ward / On-call don't need a doctor profile, so Super Admin can open them too
    // (the Doctor dashboard does need one, so it stays doctor-only).
    val canWardOnCall = isDoctor || session.isSuperAdmin
    val isNurse = session.hasRole("Nurse") || session.isSuperAdmin
    val isAdmin = session.isSuperAdmin || session.hasRole("Administrator")

    CedarScaffold(
        title = "Choose your session",
        actions = { TextButton(onClick = { viewModel.logout() }) { Text("Sign out") } },
    ) { padding ->
        Column(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp).verticalScroll(rememberScrollState()),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            Text(session.displayName ?: "Signed in", style = MaterialTheme.typography.titleMedium, color = CedarInk, modifier = Modifier.padding(top = 4.dp))
            Text(
                "Roles: " + (session.roles.takeIf { it.isNotEmpty() }?.joinToString(", ") ?: "—"),
                style = MaterialTheme.typography.bodyMedium, color = CedarMuted,
            )
            Spacer(Modifier.height(4.dp))

            AppCard("Doctor", "Rounds, consultation, prescriptions", isDoctor, onOpenDoctor)
            AppCard("Ward Doctor (RMO)", "Round list, orders, discharge", canWardOnCall, onOpenWard)
            AppCard("On-call", "Job queue, bleeps, A-to-E, order sets", canWardOnCall, onOpenOnCall)
            AppCard("Nurse", "Shift, rapid response, tasks, handover", isNurse, onOpenNurse)
            AppCard("Administrator", "Hospital operations at a glance", isAdmin, onOpenAdmin)
            Spacer(Modifier.height(24.dp))
        }
    }
}

@Composable
private fun AppCard(title: String, subtitle: String, enabled: Boolean, onClick: () -> Unit) {
    Surface(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        color = CedarSurface,
        border = BorderStroke(1.dp, CedarLine),
        enabled = enabled,
        onClick = onClick,
    ) {
        Row(
            Modifier.padding(16.dp),
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.SpaceBetween,
        ) {
            Column(Modifier.weight(1f)) {
                Text(title, style = MaterialTheme.typography.titleMedium, color = CedarInk)
                Text(subtitle, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
            }
            StatusPill(if (enabled) "Open" else "No access", if (enabled) Tone.Primary else Tone.Muted)
        }
    }
}

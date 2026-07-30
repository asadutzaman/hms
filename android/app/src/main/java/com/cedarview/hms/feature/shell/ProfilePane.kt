package com.cedarview.hms.feature.shell

import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.cedarview.hms.core.designsystem.component.Avatar
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted

/** Shared "Profile" tab for the staff role apps: identity card + sign out. */
@Composable
fun ProfilePane(
    name: String,
    subtitle: String,
    roles: List<String> = emptyList(),
    onSignOut: () -> Unit,
    onBack: (() -> Unit)? = null,
) {
    CedarScaffold(title = "Profile", onBack = onBack) { padding ->
        Column(Modifier.fillMaxSize().padding(padding).padding(18.dp)) {
            CedarCard {
                Row {
                    Avatar(name)
                    Column(Modifier.padding(start = 12.dp)) {
                        Text(name, style = MaterialTheme.typography.titleMedium, color = CedarInk, fontWeight = FontWeight.Bold)
                        Text(subtitle, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                    }
                }
                if (roles.isNotEmpty()) {
                    Spacer(Modifier.height(8.dp))
                    Text("Roles: " + roles.joinToString(", "), style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                }
            }
            Spacer(Modifier.height(16.dp))
            PrimaryButton("Sign out", onClick = onSignOut)
        }
    }
}

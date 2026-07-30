package com.cedarview.hms.feature.patient.doctors

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.FlowRow
import androidx.compose.foundation.layout.ExperimentalLayoutApi
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.common.UiState
import com.cedarview.hms.core.designsystem.component.CedarCard
import com.cedarview.hms.core.designsystem.component.CedarScaffold
import com.cedarview.hms.core.designsystem.component.GhostButton
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.component.SectionHeader
import com.cedarview.hms.core.designsystem.component.StatusPill
import com.cedarview.hms.core.designsystem.theme.CedarCrit
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.core.designsystem.theme.CedarPrimarySoft

@OptIn(ExperimentalLayoutApi::class)
@Composable
fun BookAppointmentScreen(
    onDone: () -> Unit,
    viewModel: BookAppointmentViewModel = hiltViewModel(),
) {
    val slots by viewModel.slots.collectAsStateWithLifecycle()
    val booking by viewModel.booking.collectAsStateWithLifecycle()
    val result by viewModel.result.collectAsStateWithLifecycle()
    val error by viewModel.error.collectAsStateWithLifecycle()

    CedarScaffold(title = "Book appointment", onBack = onDone) { padding ->
        Column(
            Modifier.fillMaxSize().padding(padding).padding(horizontal = 18.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
        ) {
            if (result != null) {
                CedarCard {
                    Text("Appointment booked", style = MaterialTheme.typography.titleMedium, color = CedarInk)
                    Text(result!!, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
                }
                PrimaryButton("Done", onClick = onDone)
                return@Column
            }

            Text("Date: ${viewModel.date}", style = MaterialTheme.typography.titleMedium, color = CedarInk)
            SectionHeader("Available slots")

            when (val s = slots) {
                is UiState.Loading -> CedarCard { Text("Loading availability…", color = CedarMuted) }
                is UiState.Error -> CedarCard { Text(s.message, color = CedarMuted) }
                is UiState.Success -> {
                    if (s.data.isEmpty()) {
                        CedarCard { Text("No published slots for this date — you can still request a visit.", color = CedarMuted) }
                    } else {
                        FlowRow(horizontalArrangement = Arrangement.spacedBy(8.dp), verticalArrangement = Arrangement.spacedBy(8.dp)) {
                            s.data.forEach { slot ->
                                val time = (slot.startTime ?: "").take(5)
                                Surface(
                                    shape = RoundedCornerShape(10.dp),
                                    color = CedarPrimarySoft,
                                    onClick = { if (time.isNotBlank()) viewModel.book(time) },
                                ) {
                                    Text(
                                        time.ifBlank { "Slot" },
                                        color = CedarPrimary,
                                        style = MaterialTheme.typography.labelLarge,
                                        modifier = Modifier.padding(horizontal = 14.dp, vertical = 9.dp),
                                    )
                                }
                            }
                        }
                    }
                }
            }

            error?.let {
                CedarCard { Text(it, color = CedarCrit) }
            }

            PrimaryButton(if (booking) "Requesting…" else "Request visit (10:00)", loading = booking) {
                viewModel.book("10:00")
            }
        }
    }
}

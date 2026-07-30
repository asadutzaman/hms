package com.cedarview.hms.feature.auth

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.cedarview.hms.core.auth.AuthMode
import com.cedarview.hms.core.designsystem.component.CedarTextField
import com.cedarview.hms.core.designsystem.component.PrimaryButton
import com.cedarview.hms.core.designsystem.theme.CedarBg
import com.cedarview.hms.core.designsystem.theme.CedarCrit
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.core.designsystem.theme.CedarPrimarySoft

@Composable
fun LoginScreen(
    onLoggedIn: (AuthMode) -> Unit,
    viewModel: LoginViewModel = hiltViewModel(),
) {
    val state by viewModel.state.collectAsStateWithLifecycle()

    LaunchedEffect(state.success) {
        state.success?.let(onLoggedIn)
    }

    Box(Modifier.fillMaxSize().background(CedarBg)) {
        Column(
            Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState())
                .padding(horizontal = 24.dp, vertical = 48.dp),
        ) {
            BrandHeader()
            Spacer(Modifier.height(28.dp))
            Segmented(
                selected = state.tab,
                onSelect = viewModel::setTab,
            )
            Spacer(Modifier.height(20.dp))

            when (state.tab) {
                LoginTab.STAFF -> StaffForm(state, viewModel)
                LoginTab.PATIENT -> PatientForm(state, viewModel)
            }

            state.error?.let {
                Spacer(Modifier.height(14.dp))
                Text(it, color = CedarCrit, style = MaterialTheme.typography.bodyMedium)
            }
            state.info?.takeIf { state.error == null }?.let {
                Spacer(Modifier.height(14.dp))
                Text(it, color = CedarMuted, style = MaterialTheme.typography.bodyMedium)
            }
        }
    }
}

@Composable
private fun BrandHeader() {
    Row(verticalAlignment = Alignment.CenterVertically) {
        Surface(shape = RoundedCornerShape(12.dp), color = CedarPrimary, modifier = Modifier.size(44.dp)) {
            Box(contentAlignment = Alignment.Center) {
                Text("+", color = Color.White, fontWeight = FontWeight.Bold, style = MaterialTheme.typography.headlineMedium)
            }
        }
        Spacer(Modifier.size(12.dp))
        Column {
            Text("Cedarview Care", style = MaterialTheme.typography.titleLarge, color = CedarInk)
            Text("Hospital Management System", style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
        }
    }
}

@Composable
private fun Segmented(selected: LoginTab, onSelect: (LoginTab) -> Unit) {
    Surface(shape = RoundedCornerShape(12.dp), color = Color(0xFFE6EAF1)) {
        Row(Modifier.fillMaxWidth().padding(3.dp)) {
            SegItem("Staff", selected == LoginTab.STAFF, Modifier.weight(1f)) { onSelect(LoginTab.STAFF) }
            SegItem("Patient", selected == LoginTab.PATIENT, Modifier.weight(1f)) { onSelect(LoginTab.PATIENT) }
        }
    }
}

@Composable
private fun SegItem(text: String, on: Boolean, modifier: Modifier, onClick: () -> Unit) {
    Surface(
        modifier = modifier.height(40.dp),
        shape = RoundedCornerShape(9.dp),
        color = if (on) Color.White else Color.Transparent,
        onClick = onClick,
    ) {
        Box(contentAlignment = Alignment.Center) {
            Text(
                text,
                style = MaterialTheme.typography.labelLarge,
                color = if (on) CedarInk else CedarMuted,
            )
        }
    }
}

@Composable
private fun StaffForm(state: LoginUiState, viewModel: LoginViewModel) {
    Column {
        Text("Staff sign in", style = MaterialTheme.typography.titleMedium, color = CedarInk)
        Spacer(Modifier.height(12.dp))
        CedarTextField(state.username, viewModel::onUsername, "Cedarview ID or email")
        Spacer(Modifier.height(12.dp))
        CedarTextField(state.password, viewModel::onPassword, "Password", isPassword = true)
        Spacer(Modifier.height(18.dp))
        PrimaryButton("Sign in", loading = state.loading, onClick = viewModel::staffLogin)
    }
}

@Composable
private fun PatientForm(state: LoginUiState, viewModel: LoginViewModel) {
    Column {
        Text("Welcome to Cedarview", style = MaterialTheme.typography.titleMedium, color = CedarInk)
        Spacer(Modifier.height(4.dp))
        Text(
            "Book appointments and read your reports.",
            style = MaterialTheme.typography.bodyMedium,
            color = CedarMuted,
        )
        Spacer(Modifier.height(14.dp))
        CedarTextField(state.email, viewModel::onEmail, "Email address", keyboardType = KeyboardType.Email)
        if (state.otpSent) {
            Spacer(Modifier.height(12.dp))
            CedarTextField(state.otp, viewModel::onOtp, "6-digit code", keyboardType = KeyboardType.Number)
        }
        Spacer(Modifier.height(18.dp))
        if (!state.otpSent) {
            PrimaryButton("Send verification code", loading = state.loading, onClick = viewModel::requestOtp)
        } else {
            PrimaryButton("Verify & continue", loading = state.loading, onClick = viewModel::verifyOtp)
        }
    }
}

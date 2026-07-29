package com.cedarview.hms.core.designsystem.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.runtime.Composable

/** App theme wrapper. Light-only for now to match the Cedarview design. */
@Composable
fun CedarTheme(content: @Composable () -> Unit) {
    MaterialTheme(
        colorScheme = CedarLightColors,
        typography = CedarTypography,
        content = content,
    )
}

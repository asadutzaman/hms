package com.cedarview.hms.core.designsystem.theme

import androidx.compose.material3.lightColorScheme
import androidx.compose.ui.graphics.Color

// Cedarview palette (mirrors the web design tokens).
val CedarPrimary = Color(0xFF2563EB)
val CedarPrimaryDark = Color(0xFF1D4ED8)
val CedarInk = Color(0xFF0D1B2E)
val CedarMuted = Color(0xFF5B6B84)
val CedarFaint = Color(0xFF93A0B4)
val CedarBg = Color(0xFFE9ECF2)
val CedarSurface = Color(0xFFFFFFFF)
val CedarLine = Color(0x140D1B2E)
val CedarOk = Color(0xFF16A34A)
val CedarWarn = Color(0xFFD97706)
val CedarCrit = Color(0xFFDC2626)
val CedarPrimarySoft = Color(0x142563EB)

val CedarLightColors = lightColorScheme(
    primary = CedarPrimary,
    onPrimary = Color.White,
    primaryContainer = CedarPrimarySoft,
    onPrimaryContainer = CedarPrimaryDark,
    background = CedarBg,
    onBackground = CedarInk,
    surface = CedarSurface,
    onSurface = CedarInk,
    surfaceVariant = Color(0xFFF1F4F9),
    onSurfaceVariant = CedarMuted,
    outline = CedarFaint,
    error = CedarCrit,
    onError = Color.White,
)

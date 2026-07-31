package com.cedarview.hms.core.designsystem.theme

import androidx.compose.material3.lightColorScheme
import androidx.compose.ui.graphics.Color

// Cedarview palette — mirrors the tokens in the "Cedarview HMS" design doc
// (Foundations section). Screen chrome is #F2F4F8, cards are white on a solid
// #E6EAF1 hairline, and every semantic tone comes as a soft/ink pair so pills,
// banners and metric tiles all read from the same source.

// Brand
val CedarPrimary = Color(0xFF2563EB)
val CedarPrimaryDark = Color(0xFF1D4ED8)
val CedarPrimarySoft = Color(0xFFEAF0FD)

// Neutrals
val CedarInk = Color(0xFF0D1B2E)
val CedarMuted = Color(0xFF5B6B84)
val CedarFaint = Color(0xFF93A0B4)
val CedarBg = Color(0xFFF2F4F8)
val CedarSurface = Color(0xFFFFFFFF)
val CedarLine = Color(0xFFE6EAF1)
val CedarDivider = Color(0xFFEEF1F6)
val CedarHairline = Color(0xFFC4CFDE)

// Semantic tones — base / soft background / on-soft text / soft border
val CedarOk = Color(0xFF16A34A)
val CedarOkSoft = Color(0xFFE7F6ED)
val CedarOkInk = Color(0xFF15803D)

val CedarWarn = Color(0xFFD97706)
val CedarWarnSoft = Color(0xFFFCF1E2)
val CedarWarnInk = Color(0xFFB45309)
val CedarWarnLine = Color(0xFFF5DFBC)

val CedarCrit = Color(0xFFDC2626)
val CedarCritSoft = Color(0xFFFCE9E9)
val CedarCritInk = Color(0xFFB91C1C)
val CedarCritLine = Color(0xFFF5C8C8)

// Dark surfaces — the inverted patient banner (N2) and the scan screen (N6)
val CedarInkSurface = Color(0xFF0D1B2E)
val CedarScanBg = Color(0xFF0B1220)
val CedarScanPane = Color(0xFF131C2E)

// Accent used by the dictation affordance on nursing notes (N8)
val CedarVoice = Color(0xFF7C4DD8)
val CedarVoiceSoft = Color(0xFFF2ECFB)

// Signed-in user's avatar chip in the app headers
val CedarAvatarSoft = Color(0xFFE0EDF4)
val CedarAvatarInk = Color(0xFF2D6A8A)

val CedarLightColors = lightColorScheme(
    primary = CedarPrimary,
    onPrimary = Color.White,
    primaryContainer = CedarPrimarySoft,
    onPrimaryContainer = CedarPrimaryDark,
    background = CedarBg,
    onBackground = CedarInk,
    surface = CedarSurface,
    onSurface = CedarInk,
    surfaceVariant = CedarBg,
    onSurfaceVariant = CedarMuted,
    outline = CedarFaint,
    outlineVariant = CedarLine,
    error = CedarCrit,
    onError = Color.White,
)

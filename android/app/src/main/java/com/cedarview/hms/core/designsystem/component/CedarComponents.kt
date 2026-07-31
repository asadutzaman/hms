package com.cedarview.hms.core.designsystem.component

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.Canvas
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.RowScope
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.fillMaxHeight
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBars
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyListScope
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.KeyboardArrowRight
import androidx.compose.material.icons.filled.Check
import androidx.compose.material.icons.filled.PriorityHigh
import androidx.compose.material.icons.filled.WarningAmber
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.Path
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.StrokeJoin
import androidx.compose.ui.graphics.drawscope.Stroke
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.cedarview.hms.core.designsystem.theme.CedarBg
import com.cedarview.hms.core.designsystem.theme.CedarCrit
import com.cedarview.hms.core.designsystem.theme.CedarCritInk
import com.cedarview.hms.core.designsystem.theme.CedarCritLine
import com.cedarview.hms.core.designsystem.theme.CedarCritSoft
import com.cedarview.hms.core.designsystem.theme.CedarDivider
import com.cedarview.hms.core.designsystem.theme.CedarFaint
import com.cedarview.hms.core.designsystem.theme.CedarHairline
import com.cedarview.hms.core.designsystem.theme.CedarInk
import com.cedarview.hms.core.designsystem.theme.CedarInkSurface
import com.cedarview.hms.core.designsystem.theme.CedarLine
import com.cedarview.hms.core.designsystem.theme.CedarMuted
import com.cedarview.hms.core.designsystem.theme.CedarOkInk
import com.cedarview.hms.core.designsystem.theme.CedarOkSoft
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.core.designsystem.theme.CedarPrimaryDark
import com.cedarview.hms.core.designsystem.theme.CedarPrimarySoft
import com.cedarview.hms.core.designsystem.theme.CedarSurface
import com.cedarview.hms.core.designsystem.theme.CedarWarnInk
import com.cedarview.hms.core.designsystem.theme.CedarWarnLine
import com.cedarview.hms.core.designsystem.theme.CedarWarnSoft

// ─────────────────────────────────────────────────────────────────────────────
// Page scaffolds
//
// The design doc has no Material top app bar: every screen is one scrolling
// column that starts with its own header and, where there is a commit action,
// ends in a sticky bottom bar. [CedarPage] is that shape; [CedarScaffold] is
// kept for the screens that still use a conventional title bar.
// ─────────────────────────────────────────────────────────────────────────────
@Composable
fun CedarPage(
    modifier: Modifier = Modifier,
    background: Color = CedarBg,
    gap: androidx.compose.ui.unit.Dp = 14.dp,
    bottomBar: @Composable () -> Unit = {},
    content: LazyListScope.() -> Unit,
) {
    Scaffold(
        modifier = modifier,
        containerColor = background,
        contentWindowInsets = WindowInsets.statusBars,
        bottomBar = bottomBar,
    ) { padding ->
        LazyColumn(
            Modifier.fillMaxSize().padding(padding),
            contentPadding = PaddingValues(start = 20.dp, end = 20.dp, top = 12.dp, bottom = 24.dp),
            verticalArrangement = Arrangement.spacedBy(gap),
            content = content,
        )
    }
}

/** Sticky footer holding a screen's primary action (N2, N3, N5, N8, N9…). */
@Composable
fun BottomActionBar(content: @Composable RowScope.() -> Unit) {
    Column(Modifier.background(CedarSurface)) {
        HorizontalDivider(color = CedarLine)
        Row(
            Modifier.fillMaxWidth().padding(horizontal = 20.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.spacedBy(10.dp),
            verticalAlignment = Alignment.CenterVertically,
            content = content,
        )
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CedarScaffold(
    title: String,
    onBack: (() -> Unit)? = null,
    actions: @Composable RowScope.() -> Unit = {},
    content: @Composable (PaddingValues) -> Unit,
) {
    Scaffold(
        containerColor = CedarBg,
        topBar = {
            TopAppBar(
                title = { Text(title, style = MaterialTheme.typography.titleLarge) },
                navigationIcon = {
                    if (onBack != null) {
                        IconButton(onClick = onBack) {
                            Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Back", tint = CedarPrimary)
                        }
                    }
                },
                actions = actions,
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = CedarBg,
                    titleContentColor = CedarInk,
                ),
            )
        },
        content = content,
    )
}

// ─────────────────────────────────────────────────────────────────────────────
// Headers
// ─────────────────────────────────────────────────────────────────────────────
/** Landing-screen header: small context line over a large display title (N1, N5, N10). */
@Composable
fun HeroHeader(
    eyebrow: String,
    title: String,
    modifier: Modifier = Modifier,
    trailing: @Composable RowScope.() -> Unit = {},
) {
    Row(
        modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.spacedBy(12.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Column(Modifier.weight(1f)) {
            Text(eyebrow, fontSize = 13.sp, color = CedarMuted)
            Text(title, fontSize = 24.sp, fontWeight = FontWeight.Bold, color = CedarInk, letterSpacing = (-0.4).sp)
        }
        trailing()
    }
}

/** Detail-screen header: round back button, title over subtitle, optional pill (N2, N3, N4…). */
@Composable
fun ScreenHeader(
    title: String,
    subtitle: String? = null,
    onBack: (() -> Unit)? = null,
    modifier: Modifier = Modifier,
    trailing: @Composable RowScope.() -> Unit = {},
) {
    Row(
        modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.spacedBy(12.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        if (onBack != null) {
            CircleButton(Icons.AutoMirrored.Filled.ArrowBack, "Back", onClick = onBack)
        }
        Column(Modifier.weight(1f)) {
            Text(title, fontSize = 17.sp, fontWeight = FontWeight.SemiBold, color = CedarInk)
            if (subtitle != null) Text(subtitle, fontSize = 12.sp, color = CedarMuted)
        }
        trailing()
    }
}

/** 40dp round white control used for back / bell / close in the design headers. */
@Composable
fun CircleButton(
    icon: ImageVector,
    contentDescription: String?,
    modifier: Modifier = Modifier,
    tint: Color = CedarInk,
    badge: Boolean = false,
    onClick: (() -> Unit)? = null,
) {
    Box(modifier.size(42.dp), contentAlignment = Alignment.Center) {
        Surface(
            shape = CircleShape,
            color = CedarSurface,
            border = BorderStroke(1.dp, CedarLine),
            modifier = Modifier.size(42.dp).let { if (onClick != null) it.clickable(onClick = onClick) else it },
        ) {
            Box(contentAlignment = Alignment.Center) {
                Icon(icon, contentDescription, tint = tint, modifier = Modifier.size(19.dp))
            }
        }
        if (badge) {
            Box(
                Modifier.align(Alignment.TopEnd).padding(top = 8.dp, end = 9.dp)
                    .size(9.dp).background(CedarCrit, CircleShape)
                    .border(1.5.dp, CedarSurface, CircleShape),
            )
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Buttons
// ─────────────────────────────────────────────────────────────────────────────
@Composable
fun PrimaryButton(
    text: String,
    modifier: Modifier = Modifier,
    enabled: Boolean = true,
    loading: Boolean = false,
    container: Color = CedarPrimary,
    onClick: () -> Unit,
) {
    Button(
        onClick = onClick,
        enabled = enabled && !loading,
        modifier = modifier.fillMaxWidth().height(52.dp),
        shape = RoundedCornerShape(15.dp),
        colors = ButtonDefaults.buttonColors(containerColor = container, contentColor = Color.White),
    ) {
        if (loading) {
            CircularProgressIndicator(modifier = Modifier.size(20.dp), color = Color.White, strokeWidth = 2.dp)
        } else {
            Text(text, fontSize = 16.sp, fontWeight = FontWeight.SemiBold)
        }
    }
}

@Composable
fun GhostButton(text: String, modifier: Modifier = Modifier, onClick: () -> Unit) {
    OutlinedButton(
        onClick = onClick,
        modifier = modifier.fillMaxWidth().height(50.dp),
        shape = RoundedCornerShape(14.dp),
        border = BorderStroke(1.dp, CedarLine),
    ) {
        Text(text, color = CedarPrimary, style = MaterialTheme.typography.labelLarge)
    }
}

/** Filled-tonal action: soft blue on white, the design's secondary footer button. */
@Composable
fun SoftButton(text: String, modifier: Modifier = Modifier, enabled: Boolean = true, onClick: () -> Unit) {
    Button(
        onClick = onClick,
        enabled = enabled,
        modifier = modifier.fillMaxWidth().height(50.dp),
        shape = RoundedCornerShape(14.dp),
        colors = ButtonDefaults.buttonColors(
            containerColor = CedarPrimarySoft,
            contentColor = CedarPrimary,
            disabledContainerColor = CedarBg,
            disabledContentColor = CedarFaint,
        ),
    ) {
        Text(text, fontSize = 15.sp, fontWeight = FontWeight.SemiBold)
    }
}

/** Compact pill action sitting inside a row — the MAR "Give" button (N2). */
@Composable
fun RowAction(
    text: String,
    modifier: Modifier = Modifier,
    container: Color = CedarPrimary,
    contentColor: Color = Color.White,
    onClick: () -> Unit,
) {
    Surface(
        shape = RoundedCornerShape(12.dp),
        color = container,
        modifier = modifier.clickable(onClick = onClick),
    ) {
        Text(
            text,
            color = contentColor,
            fontSize = 13.sp,
            fontWeight = FontWeight.Bold,
            textAlign = TextAlign.Center,
            modifier = Modifier.width(72.dp).padding(vertical = 10.dp, horizontal = 8.dp),
        )
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Text field
// ─────────────────────────────────────────────────────────────────────────────
@Composable
fun CedarTextField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier,
    keyboardType: KeyboardType = KeyboardType.Text,
    isPassword: Boolean = false,
    singleLine: Boolean = true,
    minLines: Int = 1,
) {
    OutlinedTextField(
        value = value,
        onValueChange = onValueChange,
        label = { Text(label, fontSize = 13.sp) },
        singleLine = singleLine,
        minLines = minLines,
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(12.dp),
        keyboardOptions = KeyboardOptions(keyboardType = keyboardType),
        visualTransformation = if (isPassword) PasswordVisualTransformation() else VisualTransformation.None,
        colors = OutlinedTextFieldDefaults.colors(
            unfocusedBorderColor = CedarLine,
            focusedBorderColor = CedarPrimary,
            unfocusedContainerColor = CedarSurface,
            focusedContainerColor = CedarSurface,
            unfocusedLabelColor = CedarFaint,
            focusedLabelColor = CedarPrimary,
        ),
    )
}

// ─────────────────────────────────────────────────────────────────────────────
// Cards + rows
// ─────────────────────────────────────────────────────────────────────────────
@Composable
fun CedarCard(
    modifier: Modifier = Modifier,
    color: Color = CedarSurface,
    border: Color = CedarLine,
    borderWidth: androidx.compose.ui.unit.Dp = 1.dp,
    padding: androidx.compose.ui.unit.Dp = 15.dp,
    content: @Composable ColumnScope.() -> Unit,
) {
    Surface(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        color = color,
        border = BorderStroke(borderWidth, border),
    ) {
        Column(Modifier.padding(padding), content = content)
    }
}

/**
 * A single white container whose children are separated by hairlines — the
 * design's grouped list ("My patients", "Next tasks", "Earlier today"). Rows
 * supply their own padding; use [GroupedDivider] between them.
 */
@Composable
fun GroupedCard(
    modifier: Modifier = Modifier,
    border: Color = CedarLine,
    borderWidth: androidx.compose.ui.unit.Dp = 1.dp,
    content: @Composable ColumnScope.() -> Unit,
) {
    Surface(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        color = CedarSurface,
        border = BorderStroke(borderWidth, border),
    ) {
        Column(content = content)
    }
}

@Composable
fun GroupedDivider() = HorizontalDivider(color = CedarDivider)

/** Bed-number cell that opens every patient row in the ward designs. */
@Composable
fun BedCell(number: String?, modifier: Modifier = Modifier) {
    Column(modifier.width(44.dp), horizontalAlignment = Alignment.CenterHorizontally) {
        Text(number?.takeIf { it.isNotBlank() } ?: "—", fontSize = 15.sp, fontWeight = FontWeight.Bold, color = CedarInk)
        Text("BED", fontSize = 10.sp, fontWeight = FontWeight.SemiBold, color = CedarFaint, letterSpacing = 0.4.sp)
    }
}

/** 4dp acuity rail on the left of a patient row. */
@Composable
fun AcuityRail(color: Color, modifier: Modifier = Modifier) {
    Box(modifier.width(4.dp).height(38.dp).background(color, RoundedCornerShape(2.dp)))
}

/** Fixed-width time gutter used by the task and note timelines. */
@Composable
fun TimeCell(text: String, color: Color = CedarMuted, bold: Boolean = false, modifier: Modifier = Modifier) {
    Text(
        text,
        modifier = modifier.width(52.dp),
        fontSize = 12.sp,
        fontWeight = if (bold) FontWeight.Bold else FontWeight.SemiBold,
        color = color,
    )
}

/** The design's 8×14 disclosure chevron — thinner than the Material icon. */
@Composable
fun Chevron(modifier: Modifier = Modifier, color: Color = CedarFaint) {
    Canvas(modifier.size(width = 8.dp, height = 14.dp)) {
        val sx = size.width / 8f
        val sy = size.height / 14f
        drawPath(
            path = Path().apply {
                moveTo(1f * sx, 1f * sy)
                lineTo(7f * sx, 7f * sy)
                lineTo(1f * sx, 13f * sy)
            },
            color = color,
            style = Stroke(width = 2f * sx, cap = StrokeCap.Round, join = StrokeJoin.Round),
        )
    }
}

@Composable
fun SectionTitle(text: String, modifier: Modifier = Modifier) {
    Text(text, modifier = modifier, fontSize = 16.sp, fontWeight = FontWeight.Bold, color = CedarInk)
}

@Composable
fun SectionHeader(text: String, modifier: Modifier = Modifier) {
    Text(
        text.uppercase(),
        fontSize = 11.sp,
        fontWeight = FontWeight.SemiBold,
        letterSpacing = 0.6.sp,
        color = CedarFaint,
        modifier = modifier,
    )
}

@Composable
fun InfoRow(label: String, value: String?) {
    Row(Modifier.fillMaxWidth().padding(vertical = 6.dp), horizontalArrangement = Arrangement.SpaceBetween) {
        Text(label, style = MaterialTheme.typography.bodyMedium, color = CedarMuted)
        Text(value ?: "—", style = MaterialTheme.typography.bodyLarge, color = CedarInk, fontWeight = FontWeight.SemiBold)
    }
}

@Composable
fun Avatar(name: String?, modifier: Modifier = Modifier) {
    Box(modifier.size(44.dp), contentAlignment = Alignment.Center) {
        Surface(shape = RoundedCornerShape(12.dp), color = CedarPrimarySoft, modifier = Modifier.size(44.dp)) {
            Box(contentAlignment = Alignment.Center) {
                Text(initialsOf(name), color = CedarPrimary, fontWeight = FontWeight.Bold)
            }
        }
    }
}

/** Circular initials avatar (design header + dark patient banner). */
@Composable
fun AvatarCircle(
    name: String?,
    modifier: Modifier = Modifier,
    container: Color = CedarPrimarySoft,
    content: Color = CedarPrimaryDark,
) {
    Surface(shape = CircleShape, color = container, modifier = modifier.size(42.dp)) {
        Box(contentAlignment = Alignment.Center) {
            Text(initialsOf(name), color = content, fontSize = 14.sp, fontWeight = FontWeight.Bold)
        }
    }
}

fun initialsOf(name: String?): String =
    (name ?: "?").trim().split(" ").filter { it.isNotBlank() }
        .take(2).joinToString("") { it.first().uppercase() }.ifBlank { "?" }

/** Inverted patient banner that anchors the medication and care screens (N2). */
@Composable
fun PatientBanner(
    name: String,
    detail: String?,
    modifier: Modifier = Modifier,
    trailing: @Composable RowScope.() -> Unit = {},
) {
    Surface(modifier.fillMaxWidth(), shape = RoundedCornerShape(16.dp), color = CedarInkSurface) {
        Row(
            Modifier.padding(15.dp),
            horizontalArrangement = Arrangement.spacedBy(12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            AvatarCircle(name, container = Color.White.copy(alpha = 0.12f), content = Color.White)
            Column(Modifier.weight(1f)) {
                Text(name, fontSize = 15.sp, fontWeight = FontWeight.SemiBold, color = Color.White)
                if (detail != null) Text(detail, fontSize = 12.sp, color = Color.White.copy(alpha = 0.55f))
            }
            trailing()
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Tone-driven surfaces: pills, banners, stats
// ─────────────────────────────────────────────────────────────────────────────
enum class Tone { Primary, Ok, Warn, Crit, Muted }

private data class ToneColors(val soft: Color, val ink: Color, val line: Color, val base: Color)

private fun toneColors(tone: Tone): ToneColors = when (tone) {
    Tone.Ok -> ToneColors(CedarOkSoft, CedarOkInk, CedarOkSoft, com.cedarview.hms.core.designsystem.theme.CedarOk)
    Tone.Warn -> ToneColors(CedarWarnSoft, CedarWarnInk, CedarWarnLine, com.cedarview.hms.core.designsystem.theme.CedarWarn)
    Tone.Crit -> ToneColors(CedarCritSoft, CedarCritInk, CedarCritLine, CedarCrit)
    Tone.Muted -> ToneColors(CedarBg, CedarMuted, CedarLine, CedarMuted)
    Tone.Primary -> ToneColors(CedarPrimarySoft, CedarPrimaryDark, CedarPrimarySoft, CedarPrimary)
}

fun toneInk(tone: Tone): Color = toneColors(tone).ink
fun toneBase(tone: Tone): Color = toneColors(tone).base

@Composable
fun StatusPill(text: String, tone: Tone = Tone.Primary, solid: Boolean = false) {
    val c = toneColors(tone)
    Surface(shape = RoundedCornerShape(999.dp), color = if (solid) c.base else c.soft) {
        Text(
            text,
            fontSize = 11.sp,
            fontWeight = FontWeight.SemiBold,
            color = if (solid) Color.White else c.ink,
            modifier = Modifier.padding(horizontal = 9.dp, vertical = 4.dp),
        )
    }
}

/** KPI tile from the shift board — big number over a small caption. */
@Composable
fun StatTile(value: String, label: String, modifier: Modifier = Modifier, valueColor: Color = CedarInk) {
    Surface(
        modifier = modifier,
        shape = RoundedCornerShape(14.dp),
        color = CedarSurface,
        border = BorderStroke(1.dp, CedarLine),
    ) {
        Column(Modifier.padding(horizontal = 14.dp, vertical = 13.dp)) {
            Text(value, fontSize = 24.sp, fontWeight = FontWeight.Bold, color = valueColor)
            Text(label, fontSize = 11.sp, fontWeight = FontWeight.Medium, color = CedarMuted, modifier = Modifier.padding(top = 2.dp))
        }
    }
}

/** Labelled progress card ("Round progress · 3 of 8"). */
@Composable
fun ProgressCard(label: String, value: String, fraction: Float, modifier: Modifier = Modifier) {
    Surface(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        color = CedarSurface,
        border = BorderStroke(1.dp, CedarLine),
    ) {
        Column(Modifier.padding(horizontal = 14.dp, vertical = 13.dp)) {
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween) {
                Text(label, fontSize = 12.sp, fontWeight = FontWeight.SemiBold, color = CedarMuted)
                Text(value, fontSize = 12.sp, fontWeight = FontWeight.Bold, color = CedarPrimary)
            }
            Spacer(Modifier.height(9.dp))
            Box(Modifier.fillMaxWidth().height(8.dp).background(CedarPrimarySoft, RoundedCornerShape(4.dp))) {
                Box(
                    Modifier.fillMaxWidth(fraction.coerceIn(0f, 1f)).fillMaxHeight()
                        .background(CedarPrimary, RoundedCornerShape(4.dp)),
                )
            }
        }
    }
}

/** Tinted advisory strip (safety note, offline note, escalation warning). */
@Composable
fun NoticeBanner(
    text: String,
    tone: Tone = Tone.Warn,
    modifier: Modifier = Modifier,
    title: String? = null,
    icon: ImageVector = Icons.Filled.WarningAmber,
) {
    val c = toneColors(tone)
    Surface(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(14.dp),
        color = c.soft,
        border = BorderStroke(1.dp, c.line),
    ) {
        Row(
            Modifier.padding(horizontal = 14.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.spacedBy(9.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Icon(icon, contentDescription = null, tint = c.ink, modifier = Modifier.size(17.dp))
            Column(Modifier.weight(1f)) {
                if (title != null) Text(title, fontSize = 13.sp, fontWeight = FontWeight.Bold, color = c.ink)
                Text(text, fontSize = 12.5.sp, color = c.ink, lineHeight = 18.sp)
            }
        }
    }
}

/** Critical escalation callout with a leading score badge (N3 / N9). */
@Composable
fun EscalationBanner(badge: String, badgeCaption: String, title: String, body: String, modifier: Modifier = Modifier) {
    Surface(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(16.dp),
        color = CedarCritSoft,
        border = BorderStroke(1.dp, CedarCritLine),
    ) {
        Row(
            Modifier.padding(15.dp),
            horizontalArrangement = Arrangement.spacedBy(13.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            Column(
                Modifier.size(52.dp).background(CedarCrit, RoundedCornerShape(16.dp)),
                horizontalAlignment = Alignment.CenterHorizontally,
                verticalArrangement = Arrangement.Center,
            ) {
                Text(badge, fontSize = 19.sp, fontWeight = FontWeight.ExtraBold, color = Color.White)
                Text(badgeCaption, fontSize = 9.sp, fontWeight = FontWeight.Bold, color = Color.White.copy(alpha = 0.8f), letterSpacing = 0.5.sp)
            }
            Column(Modifier.weight(1f)) {
                Text(title, fontSize = 14.sp, fontWeight = FontWeight.Bold, color = CedarCritInk)
                Text(body, fontSize = 12.5.sp, color = CedarCritInk.copy(alpha = 0.8f), lineHeight = 18.sp)
            }
        }
    }
}

/** One S/B/A/R paragraph: square letter chip beside the text. */
@Composable
fun SbarLine(letter: String, text: String, modifier: Modifier = Modifier) {
    Row(modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(10.dp)) {
        Box(
            Modifier.size(22.dp).background(CedarPrimarySoft, RoundedCornerShape(7.dp)),
            contentAlignment = Alignment.Center,
        ) {
            Text(letter, fontSize = 11.sp, fontWeight = FontWeight.Bold, color = CedarPrimaryDark)
        }
        Text(text, fontSize = 13.sp, color = CedarInk, lineHeight = 19.sp, modifier = Modifier.weight(1f))
    }
}

/** Observation tile: reading, previous value and a trend note (N3 vitals grid). */
@Composable
fun MetricTile(
    label: String,
    value: String,
    modifier: Modifier = Modifier,
    previous: String? = null,
    note: String? = null,
    tone: Tone = Tone.Muted,
) {
    val critical = tone == Tone.Crit
    val valueColor = when (tone) {
        Tone.Crit -> CedarCrit
        Tone.Muted -> CedarInk
        else -> CedarInk
    }
    Surface(
        modifier = modifier,
        shape = RoundedCornerShape(16.dp),
        color = if (critical) Color(0xFFFFF7F7) else CedarSurface,
        border = BorderStroke(if (critical) 1.5.dp else 1.dp, if (critical) CedarCrit else CedarLine),
    ) {
        Column(Modifier.padding(14.dp)) {
            Row(Modifier.fillMaxWidth(), horizontalArrangement = Arrangement.SpaceBetween, verticalAlignment = Alignment.CenterVertically) {
                Text(
                    label,
                    fontSize = 10.sp,
                    fontWeight = FontWeight.SemiBold,
                    letterSpacing = 0.6.sp,
                    color = if (critical) CedarCritInk else CedarFaint,
                )
                if (previous != null) Text(previous, fontSize = 10.sp, color = CedarFaint)
            }
            Text(value, fontSize = 26.sp, fontWeight = FontWeight.Bold, color = valueColor, modifier = Modifier.padding(top = 6.dp))
            if (note != null) {
                Text(note, fontSize = 11.sp, fontWeight = FontWeight.SemiBold, color = toneColors(tone).ink, modifier = Modifier.padding(top = 4.dp))
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Selection controls
// ─────────────────────────────────────────────────────────────────────────────
/** 28dp rounded-square checkbox from the task and checklist screens. */
@Composable
fun SquareCheck(checked: Boolean, modifier: Modifier = Modifier, onToggle: () -> Unit) {
    Box(
        modifier
            .size(28.dp)
            .background(if (checked) CedarPrimary else Color.Transparent, RoundedCornerShape(9.dp))
            .border(2.dp, if (checked) CedarPrimary else CedarHairline, RoundedCornerShape(9.dp))
            .clickable(onClick = onToggle),
        contentAlignment = Alignment.Center,
    ) {
        if (checked) Icon(Icons.Filled.Check, contentDescription = null, tint = Color.White, modifier = Modifier.size(17.dp))
    }
}

/** Round tick used by the five-rights list and the "who to call" options. */
@Composable
fun CircleCheck(checked: Boolean, modifier: Modifier = Modifier, size: androidx.compose.ui.unit.Dp = 24.dp) {
    Box(
        modifier
            .size(size)
            .background(if (checked) com.cedarview.hms.core.designsystem.theme.CedarOk else Color.Transparent, CircleShape)
            .border(if (checked) 0.dp else 2.dp, if (checked) Color.Transparent else CedarHairline, CircleShape),
        contentAlignment = Alignment.Center,
    ) {
        if (checked) Icon(Icons.Filled.Check, contentDescription = null, tint = Color.White, modifier = Modifier.size(size * 0.6f))
    }
}

/** Pill-shaped filter/category chip (N8 note categories). */
@Composable
fun ChoiceChip(text: String, selected: Boolean, modifier: Modifier = Modifier, onClick: () -> Unit) {
    Surface(
        modifier = modifier.clickable(onClick = onClick),
        shape = RoundedCornerShape(999.dp),
        color = if (selected) CedarPrimary else CedarSurface,
        border = if (selected) null else BorderStroke(1.dp, CedarLine),
    ) {
        Text(
            text,
            fontSize = 13.sp,
            fontWeight = if (selected) FontWeight.SemiBold else FontWeight.Medium,
            color = if (selected) Color.White else CedarMuted,
            modifier = Modifier.padding(horizontal = 14.dp, vertical = 8.dp),
        )
    }
}

/** Equal-width segment used for intake/output and the site-check row (N7). */
@Composable
fun SegmentButton(
    text: String,
    selected: Boolean,
    modifier: Modifier = Modifier,
    tone: Tone = Tone.Primary,
    onClick: () -> Unit,
) {
    val c = toneColors(tone)
    Box(
        modifier
            .height(42.dp)
            .background(if (selected) c.base else CedarBg, RoundedCornerShape(11.dp))
            .clickable(onClick = onClick),
        contentAlignment = Alignment.Center,
    ) {
        Text(
            text,
            fontSize = 13.sp,
            fontWeight = if (selected) FontWeight.SemiBold else FontWeight.Medium,
            color = if (selected) Color.White else CedarMuted,
        )
    }
}

/** 0–10 pain scale as tappable segments (N3). */
@Composable
fun PainScale(value: Int?, modifier: Modifier = Modifier, onSelect: (Int) -> Unit) {
    Row(modifier.fillMaxWidth(), horizontalArrangement = Arrangement.spacedBy(7.dp)) {
        listOf(0, 2, 4, 6, 8, 10).forEach { score ->
            val selected = value == score
            Box(
                Modifier.weight(1f).height(40.dp)
                    .background(if (selected) CedarPrimary else CedarBg, RoundedCornerShape(10.dp))
                    .clickable { onSelect(score) },
                contentAlignment = Alignment.Center,
            ) {
                Text(
                    score.toString(),
                    fontSize = 14.sp,
                    fontWeight = if (selected) FontWeight.Bold else FontWeight.SemiBold,
                    color = if (selected) Color.White else CedarMuted,
                )
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// State views
// ─────────────────────────────────────────────────────────────────────────────
@Composable
fun LoadingView(modifier: Modifier = Modifier) {
    Box(modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
        CircularProgressIndicator(color = CedarPrimary)
    }
}

@Composable
fun ErrorView(message: String, modifier: Modifier = Modifier, onRetry: (() -> Unit)? = null) {
    Column(
        modifier.fillMaxSize().padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center,
    ) {
        Text(message, style = MaterialTheme.typography.bodyLarge, color = CedarMuted, textAlign = TextAlign.Center)
        if (onRetry != null) {
            GhostButton("Try again", modifier = Modifier.padding(top = 12.dp), onClick = onRetry)
        }
    }
}

@Composable
fun EmptyView(message: String, modifier: Modifier = Modifier) {
    Box(modifier.fillMaxSize().padding(24.dp), contentAlignment = Alignment.Center) {
        Text(message, style = MaterialTheme.typography.bodyMedium, color = CedarFaint)
    }
}

/** Inline placeholder for an empty section inside a scrolling page. */
@Composable
fun EmptyCard(message: String, modifier: Modifier = Modifier) {
    CedarCard(modifier) {
        Row(verticalAlignment = Alignment.CenterVertically, horizontalArrangement = Arrangement.spacedBy(10.dp)) {
            Icon(Icons.Filled.PriorityHigh, contentDescription = null, tint = CedarFaint, modifier = Modifier.size(16.dp))
            Text(message, fontSize = 13.sp, color = CedarMuted)
        }
    }
}

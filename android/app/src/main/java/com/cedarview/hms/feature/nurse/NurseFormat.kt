package com.cedarview.hms.feature.nurse

import com.cedarview.hms.core.designsystem.component.Tone
import com.cedarview.hms.data.remote.dto.BedTileDto
import com.cedarview.hms.data.remote.dto.VitalDto
import java.text.ParseException
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Date
import java.util.Locale

// Small formatting/derivation helpers shared by the nurse screens. Timestamps
// arrive as "yyyy-MM-dd HH:mm:ss", so they are sliced rather than parsed —
// java.time is not available on this module's minSdk (24) without desugaring.

/** "10:00" from a server timestamp, or "—". */
fun clockTime(ts: String?): String {
    val t = ts?.trim().orEmpty()
    val time = t.substringAfter(' ', "").ifBlank { t }
    return time.take(5).takeIf { it.length == 5 && it[2] == ':' } ?: "—"
}

/** "25 Jul · 10:00" for history rows. */
fun stampLabel(ts: String?): String {
    val t = ts?.trim().orEmpty()
    if (t.length < 10) return "—"
    val month = MONTHS.getOrNull(t.substring(5, 7).toIntOrNull()?.minus(1) ?: -1) ?: return "—"
    val day = t.substring(8, 10).trimStart('0')
    val time = clockTime(t)
    return if (time == "—") "$day $month" else "$day $month · $time"
}

private val MONTHS = listOf("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec")

/** "Day shift · 7 AM – 7 PM · Ward 3E" — the shift-board eyebrow. */
fun shiftEyebrow(ward: String?): String {
    val label = if (Calendar.getInstance().get(Calendar.HOUR_OF_DAY) in 7..18) {
        "Day shift · 7 AM – 7 PM"
    } else {
        "Night shift · 7 PM – 7 AM"
    }
    return listOfNotNull(label, ward?.takeIf { it.isNotBlank() }).joinToString(" · ")
}

/** "Day → Night" / "Night → Day", the default handover label. */
fun currentShiftTransition(): String =
    if (Calendar.getInstance().get(Calendar.HOUR_OF_DAY) in 7..18) "Day → Night" else "Night → Day"

/**
 * Acuity rail + pill for a bed row. The shift-board payload carries no EWS or
 * acuity score, so this reads the ward/bed type — an ICU or HDU bed is a watch
 * bed, an emergency or post-op bed is an observation bed, the rest are stable.
 */
fun acuityTone(tile: BedTileDto): Tone {
    val hay = "${tile.wardName.orEmpty()} ${tile.bedType.orEmpty()}".lowercase()
    return when {
        listOf("icu", "hdu", "ccu", "critical", "intensive").any { it in hay } -> Tone.Crit
        listOf("emergency", "er ", "post-op", "postop", "recovery", "observation", "hd ").any { it in hay } -> Tone.Warn
        else -> Tone.Ok
    }
}

fun acuityLabel(tile: BedTileDto): String = when (acuityTone(tile)) {
    Tone.Crit -> "Watch"
    Tone.Warn -> "Obs"
    else -> "Stable"
}

/** "Pneumonia · day 3 · Ward 3E" — the clinical line under a bed row. */
fun bedRowSummary(tile: BedTileDto): String =
    listOfNotNull(
        tile.diagnosis?.takeIf { it.isNotBlank() },
        admissionDayLabel(tile.admissionDate),
        tile.wardName?.takeIf { it.isNotBlank() },
    ).joinToString(" · ").ifBlank { "Inpatient" }

/** "day 3", counting the admission date as day 1. */
fun admissionDayLabel(admissionDate: String?): String? =
    daysSince(admissionDate)?.let { "day ${it + 1}" }

private fun daysSince(date: String?): Int? {
    val day = date?.trim()?.take(10)?.takeIf { it.length == 10 } ?: return null
    return try {
        val fmt = SimpleDateFormat("yyyy-MM-dd", Locale.US)
        val then = fmt.parse(day) ?: return null
        val today = fmt.parse(fmt.format(Date())) ?: return null
        ((today.time - then.time) / 86_400_000L).toInt().takeIf { it >= 0 }
    } catch (e: ParseException) {
        null
    }
}

fun priorityTone(priority: String?): Tone = when (priority?.lowercase()) {
    "critical" -> Tone.Crit
    "urgent" -> Tone.Warn
    else -> Tone.Primary
}

// ── Vitals ───────────────────────────────────────────────────────────────────

/** SpO₂ below 92% is the design's escalation trigger on the vitals screen. */
const val SPO2_ESCALATION_THRESHOLD = 92

fun VitalDto.bpText(): String = bpDisplay
    ?: listOfNotNull(bpSystolic, bpDiastolic).takeIf { it.size == 2 }?.joinToString("/")
    ?: "—"

/** "was 96" for the previous reading of a metric. */
fun previousLabel(value: Any?): String? = value?.let { "was $it" }

/** Trend note comparing a new reading against the previous one. */
fun trendNote(current: Int?, previous: Int?, higherIsWorse: Boolean): Pair<String, Tone>? {
    if (current == null || previous == null || current == previous) return null
    val rising = current > previous
    val worse = rising == higherIsWorse
    return (if (rising) "Trending up" else "Trending down") to (if (worse) Tone.Warn else Tone.Ok)
}

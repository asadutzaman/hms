package com.cedarview.hms.core.designsystem.component

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.CompositionLocalProvider
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.runtime.staticCompositionLocalOf
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.cedarview.hms.core.designsystem.theme.CedarFaint
import com.cedarview.hms.core.designsystem.theme.CedarLine
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.core.designsystem.theme.CedarSurface

/** A bottom-navigation tab: label, icon and its full-screen content. */
data class CedarTab(
    val label: String,
    val icon: ImageVector,
    val content: @Composable () -> Unit,
)

/**
 * Lets a screen jump to a sibling tab by label (the shift board's task rows
 * open the Tasks tab). Defaults to a no-op outside a [TabbedShell].
 */
val LocalTabNavigator = staticCompositionLocalOf<(String) -> Unit> { {} }

/**
 * Bottom-tab shell used by the role apps. Each tab's content is a self-contained
 * screen (typically its own [CedarPage]); this only supplies the bottom bar and
 * switches which tab is shown. The bar follows the design doc: flat white, a
 * hairline top border, 22dp icons over 10sp labels and no selection pill.
 */
@Composable
fun TabbedShell(tabs: List<CedarTab>, modifier: Modifier = Modifier) {
    var index by rememberSaveable { mutableIntStateOf(0) }
    val navigate: (String) -> Unit = { label ->
        tabs.indexOfFirst { it.label.equals(label, ignoreCase = true) }
            .takeIf { it >= 0 }
            ?.let { index = it }
    }

    CompositionLocalProvider(LocalTabNavigator provides navigate) {
        Column(modifier.fillMaxSize()) {
            Box(Modifier.weight(1f)) {
                tabs.getOrNull(index)?.content?.invoke()
            }
            Column(Modifier.background(CedarSurface)) {
                HorizontalDivider(color = CedarLine)
                Row(
                    Modifier.fillMaxWidth().navigationBarsPadding()
                        .padding(horizontal = 8.dp, vertical = 10.dp),
                ) {
                    tabs.forEachIndexed { i, tab ->
                        val selected = i == index
                        val tint = if (selected) CedarPrimary else CedarFaint
                        Column(
                            Modifier.weight(1f).clickable { index = i }.padding(vertical = 2.dp),
                            horizontalAlignment = Alignment.CenterHorizontally,
                            verticalArrangement = Arrangement.spacedBy(4.dp),
                        ) {
                            Icon(tab.icon, contentDescription = tab.label, tint = tint, modifier = Modifier.size(22.dp))
                            Text(
                                tab.label,
                                fontSize = 10.sp,
                                fontWeight = if (selected) FontWeight.SemiBold else FontWeight.Medium,
                                color = tint,
                            )
                        }
                    }
                }
            }
        }
    }
}

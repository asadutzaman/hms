package com.cedarview.hms.core.designsystem.component

import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.weight
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.NavigationBarItemDefaults
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import com.cedarview.hms.core.designsystem.theme.CedarFaint
import com.cedarview.hms.core.designsystem.theme.CedarPrimary
import com.cedarview.hms.core.designsystem.theme.CedarPrimarySoft
import com.cedarview.hms.core.designsystem.theme.CedarSurface

/** A bottom-navigation tab: label, icon and its full-screen content. */
data class CedarTab(
    val label: String,
    val icon: ImageVector,
    val content: @Composable () -> Unit,
)

/**
 * Bottom-tab shell used by the role apps. Each tab's content is a self-contained
 * screen (typically its own [CedarScaffold]); this only supplies the bottom bar
 * and switches which tab is shown.
 */
@Composable
fun TabbedShell(tabs: List<CedarTab>, modifier: Modifier = Modifier) {
    var index by rememberSaveable { mutableIntStateOf(0) }
    Column(modifier.fillMaxSize()) {
        Box(Modifier.weight(1f)) {
            tabs.getOrNull(index)?.content?.invoke()
        }
        NavigationBar(containerColor = CedarSurface) {
            tabs.forEachIndexed { i, tab ->
                NavigationBarItem(
                    selected = index == i,
                    onClick = { index = i },
                    icon = { Icon(tab.icon, contentDescription = tab.label) },
                    label = { Text(tab.label, style = MaterialTheme.typography.labelSmall) },
                    colors = NavigationBarItemDefaults.colors(
                        selectedIconColor = CedarPrimary,
                        selectedTextColor = CedarPrimary,
                        indicatorColor = CedarPrimarySoft,
                        unselectedIconColor = CedarFaint,
                        unselectedTextColor = CedarFaint,
                    ),
                )
            }
        }
    }
}

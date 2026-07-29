package com.cedarview.hms

import android.app.Application
import dagger.hilt.android.HiltAndroidApp

/** Application entry point. @HiltAndroidApp bootstraps the Hilt DI graph. */
@HiltAndroidApp
class CedarApp : Application()

package com.cedarview.hms.navigation

import androidx.compose.animation.AnimatedContentTransitionScope
import androidx.compose.animation.core.tween
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.cedarview.hms.core.auth.AuthMode
import com.cedarview.hms.feature.admin.AdminShell
import com.cedarview.hms.feature.auth.LoginScreen
import com.cedarview.hms.feature.doctor.DoctorShell
import com.cedarview.hms.feature.nurse.NurseShell
import com.cedarview.hms.feature.oncall.OnCallShell
import com.cedarview.hms.feature.patient.PatientShell
import com.cedarview.hms.feature.patient.doctors.BookAppointmentScreen
import com.cedarview.hms.feature.patient.doctors.FindDoctorScreen
import com.cedarview.hms.feature.shell.AppSessionViewModel
import com.cedarview.hms.feature.shell.LauncherScreen
import com.cedarview.hms.feature.ward.WardShell

/**
 * Single navigation graph. Start destination is derived from the persisted
 * session; login routes per-mode; logout is handled centrally by observing the
 * session. All transitions use a horizontal slide + fade.
 */
@Composable
fun CedarNavHost() {
    val navController = rememberNavController()
    val appSession: AppSessionViewModel = hiltViewModel()
    val session by appSession.session.collectAsStateWithLifecycle()
    val signOut: () -> Unit = { appSession.logout() }

    LaunchedEffect(session.isAuthenticated) {
        if (!session.isAuthenticated) {
            navController.navigate(Routes.LOGIN) {
                popUpTo(0) { inclusive = true }
                launchSingleTop = true
            }
        }
    }

    // Resolved once, from the session hydrated before the first composition.
    // Passing a *changing* startDestination rebuilds the graph and resets the back
    // stack, which destroys the login destination — and with it the coroutine still
    // finishing the sign-in. Login and logout navigate explicitly instead.
    val start = remember {
        when {
            !session.isAuthenticated -> Routes.LOGIN
            session.mode == AuthMode.PATIENT -> Routes.PATIENT
            else -> Routes.LAUNCHER
        }
    }

    val d = 300

    NavHost(
        navController = navController,
        startDestination = start,
        enterTransition = { slideIntoContainer(AnimatedContentTransitionScope.SlideDirection.Left, tween(d)) + fadeIn(tween(d)) },
        exitTransition = { slideOutOfContainer(AnimatedContentTransitionScope.SlideDirection.Left, tween(d)) + fadeOut(tween(d)) },
        popEnterTransition = { slideIntoContainer(AnimatedContentTransitionScope.SlideDirection.Right, tween(d)) + fadeIn(tween(d)) },
        popExitTransition = { slideOutOfContainer(AnimatedContentTransitionScope.SlideDirection.Right, tween(d)) + fadeOut(tween(d)) },
    ) {
        composable(Routes.LOGIN) {
            LoginScreen(onLoggedIn = { mode ->
                val dest = if (mode == AuthMode.PATIENT) Routes.PATIENT else Routes.LAUNCHER
                navController.navigate(dest) {
                    popUpTo(Routes.LOGIN) { inclusive = true }
                    launchSingleTop = true
                }
            })
        }

        composable(Routes.LAUNCHER) {
            LauncherScreen(
                onOpenDoctor = { navController.navigate(Routes.DOCTOR) },
                onOpenWard = { navController.navigate(Routes.WARD) },
                onOpenNurse = { navController.navigate(Routes.NURSE) },
                onOpenOnCall = { navController.navigate(Routes.ONCALL) },
                onOpenAdmin = { navController.navigate(Routes.ADMIN) },
            )
        }

        // ---- Patient app ----
        composable(Routes.PATIENT) {
            PatientShell(
                onSignOut = signOut,
                onFindDoctor = { navController.navigate(Routes.PATIENT_FIND_DOCTOR) },
            )
        }
        composable(Routes.PATIENT_FIND_DOCTOR) {
            FindDoctorScreen(
                onBack = { navController.popBackStack() },
                onBook = { doctorId -> navController.navigate(Routes.patientBook(doctorId)) },
            )
        }
        composable(
            Routes.PATIENT_BOOK,
            arguments = listOf(navArgument("doctorId") { type = NavType.StringType }),
        ) {
            BookAppointmentScreen(onDone = { navController.popBackStack() })
        }

        // ---- Staff role apps ----
        composable(Routes.DOCTOR) { DoctorShell(onSignOut = signOut) }
        composable(Routes.WARD) { WardShell(onSignOut = signOut) }
        composable(Routes.NURSE) { NurseShell(onSignOut = signOut) }
        composable(Routes.ONCALL) { OnCallShell(onSignOut = signOut) }
        composable(Routes.ADMIN) { AdminShell(onSignOut = signOut) }
    }
}

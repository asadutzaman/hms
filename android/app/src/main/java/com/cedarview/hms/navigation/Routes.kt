package com.cedarview.hms.navigation

/** Central route table (mirrors the frontend's route registration). */
object Routes {
    const val LOGIN = "login"
    const val LAUNCHER = "launcher"

    // Patient app (bottom-tab shell + detail screens)
    const val PATIENT = "patient"
    const val PATIENT_FIND_DOCTOR = "patient/find-doctor"
    const val PATIENT_BOOK = "patient/book/{doctorId}"
    fun patientBook(doctorId: Int) = "patient/book/$doctorId"

    // Staff role apps (bottom-tab shells)
    const val DOCTOR = "doctor"
    const val WARD = "ward"
    const val NURSE = "nurse"
    const val ONCALL = "oncall"
    const val ADMIN = "admin"
}

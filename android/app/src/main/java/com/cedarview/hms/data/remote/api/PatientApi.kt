package com.cedarview.hms.data.remote.api

import com.cedarview.hms.core.network.MobileEnvelope
import com.cedarview.hms.data.remote.dto.AppointmentDto
import com.cedarview.hms.data.remote.dto.BillsData
import com.cedarview.hms.data.remote.dto.BookAppointmentRequest
import com.cedarview.hms.data.remote.dto.DoctorDto
import com.cedarview.hms.data.remote.dto.HomeData
import com.cedarview.hms.data.remote.dto.LabReportDto
import com.cedarview.hms.data.remote.dto.PrescriptionDto
import com.cedarview.hms.data.remote.dto.SlotDto
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Path
import retrofit2.http.Query

/** Patient app endpoints (patient bearer token). */
interface PatientApi {

    @GET("patient/home")
    suspend fun home(): MobileEnvelope<HomeData>

    @GET("patient/doctors")
    suspend fun doctors(
        @Query("department_id") departmentId: Int? = null,
        @Query("search") search: String? = null,
    ): MobileEnvelope<List<DoctorDto>>

    @GET("patient/doctors/{id}/slots")
    suspend fun slots(@Path("id") doctorId: Int, @Query("date") date: String): MobileEnvelope<List<SlotDto>>

    @GET("patient/appointments")
    suspend fun appointments(): MobileEnvelope<List<AppointmentDto>>

    @POST("patient/appointments")
    suspend fun book(@Body body: BookAppointmentRequest): MobileEnvelope<AppointmentDto>

    @GET("patient/prescriptions")
    suspend fun prescriptions(): MobileEnvelope<List<PrescriptionDto>>

    @GET("patient/lab-reports")
    suspend fun labReports(): MobileEnvelope<List<LabReportDto>>

    @GET("patient/bills")
    suspend fun bills(): MobileEnvelope<BillsData>
}

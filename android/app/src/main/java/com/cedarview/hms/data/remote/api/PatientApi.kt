package com.cedarview.hms.data.remote.api

import com.cedarview.hms.core.network.MobileEnvelope
import com.cedarview.hms.data.remote.dto.DoctorDto
import com.cedarview.hms.data.remote.dto.HomeData
import com.cedarview.hms.data.remote.dto.SlotDto
import retrofit2.http.GET
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
    suspend fun slots(
        @Path("id") doctorId: Int,
        @Query("date") date: String,
    ): MobileEnvelope<List<SlotDto>>
}

package com.cedarview.hms.data.remote.api

import com.cedarview.hms.core.network.MobileEnvelope
import com.cedarview.hms.data.remote.dto.AdmissionRowDto
import com.cedarview.hms.data.remote.dto.AdminDashboardData
import com.cedarview.hms.data.remote.dto.BedOccupancyData
import com.cedarview.hms.data.remote.dto.EdVisitDto
import com.cedarview.hms.data.remote.dto.LiveOpsData
import com.cedarview.hms.data.remote.dto.OpdMonitorDto
import com.cedarview.hms.data.remote.dto.ReportRefDto
import com.cedarview.hms.data.remote.dto.StaffingData
import retrofit2.http.GET

/** Administrator app endpoints (Super Admin / Administrator role). */
interface AdminApi {

    @GET("admin/dashboard")
    suspend fun dashboard(): MobileEnvelope<AdminDashboardData>

    @GET("admin/bed-occupancy")
    suspend fun bedOccupancy(): MobileEnvelope<BedOccupancyData>

    @GET("admin/live-ops")
    suspend fun liveOps(): MobileEnvelope<LiveOpsData>

    @GET("admin/monitors/ipd")
    suspend fun ipdMonitor(): MobileEnvelope<List<AdmissionRowDto>>

    @GET("admin/monitors/opd")
    suspend fun opdMonitor(): MobileEnvelope<OpdMonitorDto>

    @GET("admin/monitors/emergency")
    suspend fun emergencyMonitor(): MobileEnvelope<List<EdVisitDto>>

    @GET("admin/staffing")
    suspend fun staffing(): MobileEnvelope<StaffingData>

    @GET("admin/reports")
    suspend fun reports(): MobileEnvelope<List<ReportRefDto>>
}

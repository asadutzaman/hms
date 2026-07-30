package com.cedarview.hms.data.repository

import com.cedarview.hms.core.network.apiCall
import com.cedarview.hms.data.remote.api.AdminApi
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class AdminRepository @Inject constructor(
    private val api: AdminApi,
) {
    suspend fun dashboard() = apiCall { api.dashboard() }
    suspend fun bedOccupancy() = apiCall { api.bedOccupancy() }
    suspend fun liveOps() = apiCall { api.liveOps() }
    suspend fun ipdMonitor() = apiCall { api.ipdMonitor() }
    suspend fun opdMonitor() = apiCall { api.opdMonitor() }
    suspend fun emergencyMonitor() = apiCall { api.emergencyMonitor() }
    suspend fun staffing() = apiCall { api.staffing() }
    suspend fun reports() = apiCall { api.reports() }
}

package com.cedarview.hms.data.repository

import com.cedarview.hms.core.network.apiCall
import com.cedarview.hms.data.remote.api.OnCallApi
import com.cedarview.hms.data.remote.dto.AtoeRequest
import com.cedarview.hms.data.remote.dto.BleepRequest
import com.cedarview.hms.data.remote.dto.ClinicalJobRequest
import com.cedarview.hms.data.remote.dto.HandoverRequest
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class OnCallRepository @Inject constructor(
    private val api: OnCallApi,
) {
    suspend fun console() = apiCall { api.console() }
    suspend fun jobs() = apiCall { api.jobs() }
    suspend fun createJob(request: ClinicalJobRequest) = apiCall { api.createJob(request) }
    suspend fun claimJob(id: Int) = apiCall { api.claimJob(id) }
    suspend fun completeJob(id: Int) = apiCall { api.completeJob(id) }
    suspend fun bleeps() = apiCall { api.bleeps() }
    suspend fun raiseBleep(request: BleepRequest) = apiCall { api.raiseBleep(request) }
    suspend fun acknowledgeBleep(id: Int) = apiCall { api.acknowledgeBleep(id) }
    suspend fun assessments() = apiCall { api.assessments() }
    suspend fun createAssessment(request: AtoeRequest) = apiCall { api.createAssessment(request) }
    suspend fun orderSets() = apiCall { api.orderSets() }
    suspend fun edBoard() = apiCall { api.edBoard() }
    suspend fun handovers() = apiCall { api.handovers() }
    suspend fun createHandover(request: HandoverRequest) = apiCall { api.createHandover(request) }
}

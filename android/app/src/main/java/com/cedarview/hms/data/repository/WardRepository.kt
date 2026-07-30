package com.cedarview.hms.data.repository

import com.cedarview.hms.core.network.apiCall
import com.cedarview.hms.data.remote.api.WardApi
import com.cedarview.hms.data.remote.dto.DailyReviewRequest
import com.cedarview.hms.data.remote.dto.TransferRequest
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class WardRepository @Inject constructor(
    private val api: WardApi,
) {
    suspend fun dashboard() = apiCall { api.dashboard() }
    suspend fun roundList() = apiCall { api.roundList() }
    suspend fun resultsInbox() = apiCall { api.resultsInbox() }
    suspend fun vitals(admissionId: Int) = apiCall { api.vitals(admissionId) }
    suspend fun drugChart(admissionId: Int) = apiCall { api.drugChart(admissionId) }
    suspend fun labOrders(admissionId: Int) = apiCall { api.labOrders(admissionId) }
    suspend fun radiologyOrders(admissionId: Int) = apiCall { api.radiologyOrders(admissionId) }
    suspend fun fluidBalance(admissionId: Int) = apiCall { api.fluidBalance(admissionId) }
    suspend fun dischargeSummary(admissionId: Int) = apiCall { api.dischargeSummary(admissionId) }
    suspend fun generateDischargeSummary(admissionId: Int) = apiCall { api.generateDischargeSummary(admissionId) }
    suspend fun signDischargeSummary(id: Int) = apiCall { api.signDischargeSummary(id) }
    suspend fun transfer(admissionId: Int, bedId: Int, reason: String?) =
        apiCall { api.transfer(admissionId, TransferRequest(bedId = bedId, reason = reason)) }
    suspend fun dailyReviews(admissionId: Int) = apiCall { api.dailyReviews(admissionId) }
    suspend fun createDailyReview(admissionId: Int, request: DailyReviewRequest) =
        apiCall { api.createDailyReview(admissionId, request) }
}

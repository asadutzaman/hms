package com.cedarview.hms.data.repository

import com.cedarview.hms.core.network.apiCall
import com.cedarview.hms.data.remote.api.NurseApi
import com.cedarview.hms.data.remote.dto.BarcodeVerifyRequest
import com.cedarview.hms.data.remote.dto.CodeBlueRequest
import com.cedarview.hms.data.remote.dto.DischargeChecklistRequest
import com.cedarview.hms.data.remote.dto.FluidRequest
import com.cedarview.hms.data.remote.dto.HandoverRequest
import com.cedarview.hms.data.remote.dto.MarRecordRequest
import com.cedarview.hms.data.remote.dto.NursingNoteRequest
import com.cedarview.hms.data.remote.dto.TransferRequest
import com.cedarview.hms.data.remote.dto.VitalRequest
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class NurseRepository @Inject constructor(
    private val api: NurseApi,
) {
    suspend fun shiftBoard() = apiCall { api.shiftBoard() }

    suspend fun mar(admissionId: Int) = apiCall { api.mar(admissionId) }
    suspend fun recordMar(id: Int, request: MarRecordRequest) = apiCall { api.recordMar(id, request) }
    suspend fun verifyBarcode(request: BarcodeVerifyRequest) = apiCall { api.verifyBarcode(request) }

    suspend fun vitals(admissionId: Int) = apiCall { api.vitals(admissionId) }
    suspend fun recordVitals(request: VitalRequest) = apiCall { api.recordVitals(request) }

    suspend fun fluidBalance(admissionId: Int) = apiCall { api.fluidBalance(admissionId) }
    suspend fun recordFluid(request: FluidRequest) = apiCall { api.recordFluid(request) }

    suspend fun nursingNotes(admissionId: Int) = apiCall { api.nursingNotes(admissionId) }
    suspend fun storeNursingNote(request: NursingNoteRequest) = apiCall { api.storeNursingNote(request) }

    suspend fun dischargeChecklist(admissionId: Int) = apiCall { api.dischargeChecklist(admissionId) }
    suspend fun upsertDischargeChecklist(admissionId: Int, request: DischargeChecklistRequest) =
        apiCall { api.upsertDischargeChecklist(admissionId, request) }

    suspend fun transfer(admissionId: Int, bedId: Int, reason: String?) =
        apiCall { api.transfer(admissionId, TransferRequest(bedId = bedId, reason = reason)) }

    suspend fun rapidResponse() = apiCall { api.rapidResponse() }
    suspend fun raiseRapidResponse(request: CodeBlueRequest) = apiCall { api.raiseRapidResponse(request) }
    suspend fun tasks() = apiCall { api.tasks() }
    suspend fun completeTask(id: Int) = apiCall { api.completeTask(id) }
    suspend fun handovers() = apiCall { api.handovers() }
    suspend fun createHandover(request: HandoverRequest) = apiCall { api.createHandover(request) }
}

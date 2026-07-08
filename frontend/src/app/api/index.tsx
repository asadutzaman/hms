import _ApplicationSettingApi from './ApplicationSetting.api'
import _FackApi from './Fack.api'
import _OauthApi from './Oauth.api'
// SETTING
import _GroupApi from './Setting/Group.api'
import _OrganizationApi from './Setting/Organization.api'
import _OrganogramApi from './Setting/Organogram.api'
import _PermissionApi from './Setting/Permission.api'
import _ResourceApi from './Setting/Resource.api'
import _RoleApi from './Setting/Role.api'
import _ScopeApi from './Setting/Scope.api'
import _UnitApi from './Setting/Unit.api'
import _ApproverGroupApi from './Setting/ApproverGroup.api'
import _UserApi from './Setting/User.api'
import _WorkflowApi from './Setting/Workflow.api'
import _WorkflowStepApi from './Setting/WorkflowStep.api'
// SETUP
import _DepartmentApi from './Setup/Department.api'
import _DesignationApi from './Setup/Designation.api'
import _RequisitionItemLimitApi from './Setup/RequisitionItemLimit.api'
import _SupplierApi from './Setup/Supplier.api'
import _EnumApi from './Setup/Enum.api'
import _BrandApi from './Setup/Brand.api'
import _ItemModelApi from './Setup/ItemModel.api'
import _AuthorApi from './Setup/Author.api'
import _PublisherApi from './Setup/Publisher.api'
import _BranchApi from './Setup/Branch.api'
import _ShelveApi from './Setup/Shelve.api'
import _LogisticApi from './Setup/Logistic.api'
import _AttributeApi from './Setup/Attribute.api'
import _AttributeValueApi from './Setup/AttributeValue.api'
import _ItemApi from './Setup/Item.api'
import _DrugApi from './Setup/Drug.api'
import _ItemCategoryApi from './Setup/ItemCategory.api'
import _GovtHolidayApi from './Setup/GovtHoliday.api'
// INVENTORY
import _FileApi from './Inventory/File.api'
import _RequisitionApi from './Inventory/Requisition.api'
import _RequisitionApprovalApi from './Inventory/RequisitionApproval.api'
import _GoodsReceiveNoteApi from './Inventory/GoodsReceiveNote.api'
import _GoodsReceiveNoteApprovalApi from './Inventory/GoodsReceiveNoteApproval.api'
import _PurchaseOrderApi from './Inventory/PurchaseOrder.api'
import _PurchaseOrderApprovalApi from './Inventory/PurchaseOrderApproval.api'
import _VendorQuoteApi from './Inventory/VendorQuote.api'
import _RateContractApi from './Inventory/RateContract.api'
import _StockAdjustmentApi from './Inventory/StockAdjustment.api'
import _StockAdjustmentApprovalApi from './Inventory/StockAdjustmentApproval.api'
import _WorkflowTransitionApi from './Inventory/WorkflowTransition.api'
import _ItemConsumptionApi from './Inventory/ItemConsumption.api'
import _StockTransferApi from './Inventory/StockTransfer.api'
import _StockTransferApprovalApi from './Inventory/StockTransferApproval.api'
// Report
import _ReportInvApi from './Inventory/ReportInv.api'
// COMPANY
import _EmployeeApi from './Company/Employee.api'
// PATIENT
import _PatientApi from './Patient/Patient.api'
import _PatientAllergyApi from './Patient/PatientAllergy.api'
import _PatientChronicConditionApi from './Patient/PatientChronicCondition.api'
import _TheatreApi from './Ot/Theatre.api'
import _OtBookingApi from './Ot/OtBooking.api'
import _SurgeryNoteApi from './Ot/SurgeryNote.api'
import _AnaesthesiaRecordApi from './Ot/AnaesthesiaRecord.api'
import _InsuranceClaimSettlementApi from './Insurance/InsuranceClaimSettlement.api'
import _ShiftApi from './Hr/Shift.api'
import _LeaveTypeApi from './Hr/LeaveType.api'
import _AttendanceRecordApi from './Hr/AttendanceRecord.api'
import _LeaveRequestApi from './Hr/LeaveRequest.api'
import _LeaveBalanceApi from './Hr/LeaveBalance.api'
// APPOINTMENT
import _AppointmentApi from './Appointment/Appointment.api'
import _DoctorScheduleApi from './DoctorSchedule/DoctorSchedule.api'
import _AppointmentWaitlistApi from './AppointmentWaitlist/AppointmentWaitlist.api'
// OPD
import _OpdVisitApi from './OpdVisit/OpdVisit.api'
import _OpdVitalApi from './OpdVital/OpdVital.api'
import _OpdDiagnosisApi from './OpdDiagnosis/OpdDiagnosis.api'
import _OpdPrescriptionApi from './OpdPrescription/OpdPrescription.api'
import _OpdPrescriptionItemApi from './OpdPrescriptionItem/OpdPrescriptionItem.api'
import _PrescriptionDispenseApi from './PrescriptionDispense/PrescriptionDispense.api'
import _DrugInteractionApi from './DrugInteraction/DrugInteraction.api'
import _DoctorPortalApi from './DoctorPortal/DoctorPortal.api'
import _PrescriptionTemplateApi from './PrescriptionTemplate/PrescriptionTemplate.api'
import _ReferralApi from './Referral/Referral.api'
import _OpdProcedureApi from './OpdProcedure/OpdProcedure.api'
import _Icd10CodeApi from './Icd10Code/Icd10Code.api'
import _DiagnosisTemplateApi from './DiagnosisTemplate/DiagnosisTemplate.api'
import _OpdInvestigationOrderApi from './OpdInvestigationOrder/OpdInvestigationOrder.api'
import _OpdInvestigationOrderItemApi from './OpdInvestigationOrderItem/OpdInvestigationOrderItem.api'
import _OpdBillApi from './OpdBill/OpdBill.api'
import _OpdBillItemApi from './OpdBillItem/OpdBillItem.api'
import _OpdBillPaymentApi from './OpdBillPayment/OpdBillPayment.api'
import _LabTestApi from './LabTest/LabTest.api'
// LAB (LIS)
import _LabOrderApi from './Lab/LabOrder.api'
import _LabSampleApi from './Lab/LabSample.api'
import _LabResultApi from './Lab/LabResult.api'
// IPD
import _WardApi from './Ipd/Ward.api'
import _BedApi from './Ipd/Bed.api'
import _IpdAdmissionApi from './Ipd/IpdAdmission.api'
import _IpdBillApi from './Ipd/IpdBill.api'
import _IpdBillItemApi from './Ipd/IpdBillItem.api'
import _IpdBillPaymentApi from './Ipd/IpdBillPayment.api'
import _IpdAdvancePaymentApi from './Ipd/IpdAdvancePayment.api'
import _IpdVitalApi from './Ipd/IpdVital.api'
import _IpdNursingAssessmentApi from './Ipd/IpdNursingAssessment.api'
import _IpdFluidBalanceApi from './Ipd/IpdFluidBalance.api'
import _IpdMedicationOrderApi from './Ipd/IpdMedicationOrder.api'
import _IpdMedicationAdministrationApi from './Ipd/IpdMedicationAdministration.api'
import _IpdDischargeSummaryApi from './Ipd/IpdDischargeSummary.api'
import _IpdDeathCertificateApi from './Ipd/IpdDeathCertificate.api'
// EMERGENCY
import _ErVisitApi from './Emergency/ErVisit.api'
import _ErTriageApi from './Emergency/ErTriage.api'
// PATIENT HISTORY
import _PatientHistoryApi from './PatientHistory/PatientHistory.api'
// NOTIFICATION
import _NotificationTemplateApi from './Notification/NotificationTemplate.api'
import _NotificationApi from './Notification/Notification.api'
// PATIENT ATTACHMENT
import _PatientAttachmentApi from './Patient/PatientAttachment.api'
// BACKUP
import _BackupApi from './Setting/Backup.api'
// INSURANCE / TPA
import _InsuranceCompanyApi from './Insurance/InsuranceCompany.api'
import _InsuranceSchemeApi from './Insurance/InsuranceScheme.api'
import _PreAuthorizationApi from './Insurance/PreAuthorization.api'
import _InsuranceClaimApi from './Insurance/InsuranceClaim.api'
// BILLING (Package & Refund)
import _BillingPackageApi from './Billing/BillingPackage.api'
import _BillRefundApi from './Billing/BillRefund.api'
// RADIOLOGY
import _RadiologyTestApi from './Radiology/RadiologyTest.api'
import _RadiologyReportTemplateApi from './Radiology/RadiologyReportTemplate.api'
import _RadiologyOrderApi from './Radiology/RadiologyOrder.api'
import _RadiologyReportApi from './Radiology/RadiologyReport.api'
import _PatientPortalApi from './PatientPortal/PatientPortal.api'

export const FackApi = new _FackApi()
export const OauthApi = new _OauthApi()
export const ApplicationSettingApi = new _ApplicationSettingApi()
// SETTING
export const ResourceApi = new _ResourceApi()
export const ScopeApi = new _ScopeApi()
export const RoleApi = new _RoleApi()
export const GroupApi = new _GroupApi()
export const OrganizationApi = new _OrganizationApi()
export const OrganogramApi = new _OrganogramApi()
export const UserApi = new _UserApi()
export const PermissionApi = new _PermissionApi()
export const UnitApi = new _UnitApi()
export const ApproverGroupApi = new _ApproverGroupApi()
export const WorkflowApi = new _WorkflowApi()
export const WorkflowStepApi = new _WorkflowStepApi()
export const GovtHolidayApi = new _GovtHolidayApi()
// SETUP
export const EnumApi = new _EnumApi()
export const DepartmentApi = new _DepartmentApi()
export const DesignationApi = new _DesignationApi()
export const RequisitionItemLimitApi = new _RequisitionItemLimitApi()
export const SupplierApi = new _SupplierApi()
export const ItemCategoryApi = new _ItemCategoryApi()
export const BrandApi = new _BrandApi()
export const ItemModelApi = new _ItemModelApi()
export const AuthorApi = new _AuthorApi()
export const PublisherApi = new _PublisherApi()
export const BranchApi = new _BranchApi()
export const ShelveApi = new _ShelveApi()
export const LogisticApi = new _LogisticApi()
export const AttributeApi = new _AttributeApi()
export const AttributeValueApi = new _AttributeValueApi()
export const ItemApi = new _ItemApi()
export const DrugApi = new _DrugApi()
export const DrugInteractionApi = new _DrugInteractionApi()
export const WorkflowTransitionApi = new _WorkflowTransitionApi()
// INVENTORY
export const FileApi = new _FileApi()
export const RequisitionApi = new _RequisitionApi()
export const RequisitionApprovalApi = new _RequisitionApprovalApi()
export const GoodsReceiveNoteApi = new _GoodsReceiveNoteApi()
export const GoodsReceiveNoteApprovalApi = new _GoodsReceiveNoteApprovalApi()
export const PurchaseOrderApi = new _PurchaseOrderApi()
export const PurchaseOrderApprovalApi = new _PurchaseOrderApprovalApi()
export const VendorQuoteApi = new _VendorQuoteApi()
export const RateContractApi = new _RateContractApi()
export const StockAdjustmentApi = new _StockAdjustmentApi()
export const StockAdjustmentApprovalApi = new _StockAdjustmentApprovalApi()
export const ItemConsumptionApi = new _ItemConsumptionApi()
export const StockTransferApi = new _StockTransferApi()
export const StockTransferApprovalApi = new _StockTransferApprovalApi()
// Report
export const ReportInvApi = new _ReportInvApi()
// PATIENT
export const PatientApi = new _PatientApi()
export const PatientAllergyApi = new _PatientAllergyApi()
export const PatientChronicConditionApi = new _PatientChronicConditionApi()
// SURGERY & OT (Sprint 9, F-09-xx)
export const TheatreApi = new _TheatreApi()
export const OtBookingApi = new _OtBookingApi()
export const SurgeryNoteApi = new _SurgeryNoteApi()
export const AnaesthesiaRecordApi = new _AnaesthesiaRecordApi()
// INSURANCE CLAIM SETTLEMENT (Sprint 9, F-20-05)
export const InsuranceClaimSettlementApi = new _InsuranceClaimSettlementApi()
// HR ATTENDANCE & LEAVE (Sprint 9, F-13-02/03)
export const ShiftApi = new _ShiftApi()
export const LeaveTypeApi = new _LeaveTypeApi()
export const AttendanceRecordApi = new _AttendanceRecordApi()
export const LeaveRequestApi = new _LeaveRequestApi()
export const LeaveBalanceApi = new _LeaveBalanceApi()
// COMPANY
export const EmployeeApi = new _EmployeeApi()
// APPOINTMENT
export const AppointmentApi = new _AppointmentApi()
export const DoctorScheduleApi = new _DoctorScheduleApi()
export const AppointmentWaitlistApi = new _AppointmentWaitlistApi()
// OPD
export const OpdVisitApi = new _OpdVisitApi()
export const OpdVitalApi = new _OpdVitalApi()
export const OpdDiagnosisApi = new _OpdDiagnosisApi()
export const OpdPrescriptionApi = new _OpdPrescriptionApi()
export const OpdPrescriptionItemApi = new _OpdPrescriptionItemApi()
export const PrescriptionDispenseApi = new _PrescriptionDispenseApi()
export const DoctorPortalApi = new _DoctorPortalApi()
export const PrescriptionTemplateApi = new _PrescriptionTemplateApi()
export const ReferralApi = new _ReferralApi()
export const OpdProcedureApi = new _OpdProcedureApi()
export const Icd10CodeApi = new _Icd10CodeApi()
export const DiagnosisTemplateApi = new _DiagnosisTemplateApi()
export const OpdInvestigationOrderApi = new _OpdInvestigationOrderApi()
export const OpdInvestigationOrderItemApi = new _OpdInvestigationOrderItemApi()
export const OpdBillApi = new _OpdBillApi()
export const OpdBillItemApi = new _OpdBillItemApi()
export const OpdBillPaymentApi = new _OpdBillPaymentApi()
export const LabTestApi = new _LabTestApi()
// LAB (LIS)
export const LabOrderApi = new _LabOrderApi()
export const LabSampleApi = new _LabSampleApi()
export const LabResultApi = new _LabResultApi()
// IPD
export const WardApi = new _WardApi()
export const BedApi = new _BedApi()
export const IpdAdmissionApi = new _IpdAdmissionApi()
export const IpdBillApi = new _IpdBillApi()
export const IpdBillItemApi = new _IpdBillItemApi()
export const IpdBillPaymentApi = new _IpdBillPaymentApi()
export const IpdAdvancePaymentApi = new _IpdAdvancePaymentApi()
export const IpdVitalApi = new _IpdVitalApi()
export const IpdNursingAssessmentApi = new _IpdNursingAssessmentApi()
export const IpdFluidBalanceApi = new _IpdFluidBalanceApi()
export const IpdMedicationOrderApi = new _IpdMedicationOrderApi()
export const IpdMedicationAdministrationApi = new _IpdMedicationAdministrationApi()
export const IpdDischargeSummaryApi = new _IpdDischargeSummaryApi()
export const IpdDeathCertificateApi = new _IpdDeathCertificateApi()
// EMERGENCY
export const ErVisitApi = new _ErVisitApi()
export const ErTriageApi = new _ErTriageApi()
// PATIENT HISTORY
export const PatientHistoryApi = new _PatientHistoryApi()
// NOTIFICATION
export const NotificationTemplateApi = new _NotificationTemplateApi()
export const NotificationApi = new _NotificationApi()
// PATIENT ATTACHMENT
export const PatientAttachmentApi = new _PatientAttachmentApi()
// BACKUP
export const BackupApi = new _BackupApi()
// INSURANCE / TPA
export const InsuranceCompanyApi = new _InsuranceCompanyApi()
export const InsuranceSchemeApi = new _InsuranceSchemeApi()
export const PreAuthorizationApi = new _PreAuthorizationApi()
export const InsuranceClaimApi = new _InsuranceClaimApi()
// BILLING (Package & Refund)
export const BillingPackageApi = new _BillingPackageApi()
export const BillRefundApi = new _BillRefundApi()
// RADIOLOGY
export const RadiologyTestApi = new _RadiologyTestApi()
export const RadiologyReportTemplateApi = new _RadiologyReportTemplateApi()
export const RadiologyOrderApi = new _RadiologyOrderApi()
export const RadiologyReportApi = new _RadiologyReportApi()
// PATIENT PORTAL (Sprint 8, F-17-xx)
export const PatientPortalApi = new _PatientPortalApi()

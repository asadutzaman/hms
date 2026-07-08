import {AxiosPromise} from 'axios'
import {CONSTANT_CONFIG} from '../../constants'
import {HttpService} from '../../services/http.services'

const RESOURCE_ENDPOINT = `${CONSTANT_CONFIG.SERVER_PREFIX}/report`
const endpoints = {
  // REQUISITION ANALYTIC
  getRequisitionAnalyticReport: () => `${RESOURCE_ENDPOINT}/requisition-analytic`,
  getRequisitionAnalyticReportExport: () => `${RESOURCE_ENDPOINT}/requisition-analytic-export`,
  // ITEM STOCK
  getItemStockReport: () => `${RESOURCE_ENDPOINT}/item-stock`,
  getItemStockReportExport: () => `${RESOURCE_ENDPOINT}/item-stock-export`,
  // ITEM LOW STOCK
  getItemLowStockReport: () => `${RESOURCE_ENDPOINT}/item-low-stock`,
  getItemLowStockReportExport: () => `${RESOURCE_ENDPOINT}/item-low-stock-export`,
  // ITEM REQUISITION STATUS
  getItemRequisitionStatusReport: () => `${RESOURCE_ENDPOINT}/item-requisition-status`,
  getItemRequisitionStatusReportExport: () => `${RESOURCE_ENDPOINT}/item-requisition-status-export`,
  // REQUISITION ANALYTIC
  getRequesterWiseDisbursementReport: () => `${RESOURCE_ENDPOINT}/requester-wise-disbursement`,
  getRequesterWiseDisbursementReportExport: () =>
    `${RESOURCE_ENDPOINT}/requester-wise-disbursement-export`,
  // ITEM WISE DISBURSEMENT
  getItemWiseDisbursementReport: () => `${RESOURCE_ENDPOINT}/item-wise-disbursement`,
  getItemWiseDisbursementReportExport: () => `${RESOURCE_ENDPOINT}/item-wise-disbursement-export`,
  // THANAWISE DISBURSEMENT
  getThanaWiseDisbursementReport: () => `${RESOURCE_ENDPOINT}/branch-wise-disbursement`,
  getThanaWiseDisbursementReportExport: () =>
    `${RESOURCE_ENDPOINT}/branch-wise-disbursement-export`,
  // DRUG EXPIRY
  getDrugExpiryReport: () => `${RESOURCE_ENDPOINT}/drug-expiry`,
  getDrugExpiryReportExport: () => `${RESOURCE_ENDPOINT}/drug-expiry-export`,
  // DAILY COLLECTION
  getDailyCollectionReport: () => `${RESOURCE_ENDPOINT}/daily-collection`,
  getDailyCollectionReportExport: () => `${RESOURCE_ENDPOINT}/daily-collection-export`,
  // CONTROLLED DRUG REGISTER
  getControlledDrugRegisterReport: () => `${RESOURCE_ENDPOINT}/controlled-drug-register`,
  // REORDER ALERTS
  getItemLowStockAlerts: () => `${RESOURCE_ENDPOINT}/item-low-stock-alerts`,
  // MIS EXECUTIVE DASHBOARD (Sprint 8, F-14-02)
  getMisDashboard: () => `${RESOURCE_ENDPOINT}/mis-dashboard`,
  // OCCUPANCY & REVENUE (Sprint 8, F-14-03)
  getOccupancyRevenueReport: () => `${RESOURCE_ENDPOINT}/occupancy-revenue`,
  // DOCTOR PRODUCTIVITY (Sprint 8, F-14-05)
  getDoctorProductivityReport: () => `${RESOURCE_ENDPOINT}/doctor-productivity`,
  // PHARMACY SALES ANALYTICS (Sprint 8, F-14-07)
  getPharmacySalesAnalyticsReport: () => `${RESOURCE_ENDPOINT}/pharmacy-sales-analytics`,
  // LAB REVENUE ANALYTICS (Sprint 8, F-14-08)
  getLabRevenueAnalyticsReport: () => `${RESOURCE_ENDPOINT}/lab-revenue-analytics`,
  // ATTENDANCE (Sprint 9, F-13-02)
  getAttendanceReport: () => `${RESOURCE_ENDPOINT}/attendance`,
}

export default class ReportScmApi {
  // REQUISITION ANALYTIC
  public getRequisitionAnalyticReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getRequisitionAnalyticReport()
    return HttpService.get(url, params, headers)
  }
  public getRequisitionAnalyticReportExport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getRequisitionAnalyticReportExport()
    return HttpService.exportFile(url, params, headers)
  }
  // ITEM STOCK
  public getItemStockReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getItemStockReport()
    return HttpService.get(url, params, headers)
  }
  public getItemStockReportExport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getItemStockReportExport()
    return HttpService.exportFile(url, params, headers)
  }
  // ITEM LOW STOCK
  public getItemLowStockReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getItemLowStockReport()
    return HttpService.get(url, params, headers)
  }
  public getItemLowStockReportExport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getItemLowStockReportExport()
    return HttpService.exportFile(url, params, headers)
  }
  // ITEM REQUISITION STATUS
  public getItemRequisitionStatusReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getItemRequisitionStatusReport()
    return HttpService.get(url, params, headers)
  }
  public getItemRequisitionStatusReportExport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getItemRequisitionStatusReportExport()
    return HttpService.exportFile(url, params, headers)
  }
  // REQUISITION ANALYTIC
  public getRequesterWiseDisbursementReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getRequesterWiseDisbursementReport()
    return HttpService.get(url, params, headers)
  }
  public getRequesterWiseDisbursementReportExport = (
    params = {},
    headers = {}
  ): AxiosPromise<any> => {
    const url = endpoints.getRequesterWiseDisbursementReportExport()
    return HttpService.exportFile(url, params, headers)
  }
  // ITEM WISE DISBURSEMENT
  public getItemWiseDisbursementReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getItemWiseDisbursementReport()
    return HttpService.get(url, params, headers)
  }
  public getItemWiseDisbursementReportExport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getItemWiseDisbursementReportExport()
    return HttpService.exportFile(url, params, headers)
  }
  // THANAWISE DISBURSEMENT
  public getThanaWiseDisbursementReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getThanaWiseDisbursementReport()
    return HttpService.get(url, params, headers)
  }
  public getThanaWiseDisbursementReportExport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getThanaWiseDisbursementReportExport()
    return HttpService.exportFile(url, params, headers)
  }
  // DRUG EXPIRY
  public getDrugExpiryReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getDrugExpiryReport()
    return HttpService.get(url, params, headers)
  }
  public getDrugExpiryReportExport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getDrugExpiryReportExport()
    return HttpService.exportFile(url, params, headers)
  }
  // DAILY COLLECTION
  public getDailyCollectionReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getDailyCollectionReport()
    return HttpService.get(url, params, headers)
  }
  public getDailyCollectionReportExport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getDailyCollectionReportExport()
    return HttpService.exportFile(url, params, headers)
  }
  // CONTROLLED DRUG REGISTER
  public getControlledDrugRegisterReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getControlledDrugRegisterReport()
    return HttpService.get(url, params, headers)
  }
  // REORDER ALERTS
  public getItemLowStockAlerts = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getItemLowStockAlerts()
    return HttpService.get(url, params, headers)
  }
  // MIS EXECUTIVE DASHBOARD
  public getMisDashboard = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getMisDashboard()
    return HttpService.get(url, params, headers)
  }
  // OCCUPANCY & REVENUE
  public getOccupancyRevenueReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getOccupancyRevenueReport()
    return HttpService.get(url, params, headers)
  }
  // DOCTOR PRODUCTIVITY
  public getDoctorProductivityReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getDoctorProductivityReport()
    return HttpService.get(url, params, headers)
  }
  // PHARMACY SALES ANALYTICS
  public getPharmacySalesAnalyticsReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getPharmacySalesAnalyticsReport()
    return HttpService.get(url, params, headers)
  }
  // LAB REVENUE ANALYTICS
  public getLabRevenueAnalyticsReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getLabRevenueAnalyticsReport()
    return HttpService.get(url, params, headers)
  }
  // ATTENDANCE
  public getAttendanceReport = (params = {}, headers = {}): AxiosPromise<any> => {
    const url = endpoints.getAttendanceReport()
    return HttpService.get(url, params, headers)
  }
}

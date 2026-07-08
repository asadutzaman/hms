import React from 'react';
import { Route, Routes } from 'react-router';
import MisDashboardListController from './MisDashboard/List/MisDashboardList.controller';
import OccupancyRevenueListController from './OccupancyRevenue/List/OccupancyRevenueList.controller';
import DoctorProductivityListController from './DoctorProductivity/List/DoctorProductivityList.controller';
import PharmacySalesAnalyticsListController from './PharmacySalesAnalytics/List/PharmacySalesAnalyticsList.controller';
import LabRevenueAnalyticsListController from './LabRevenueAnalytics/List/LabRevenueAnalyticsList.controller';
import AttendanceReportListController from './AttendanceReport/List/AttendanceReportList.controller';

const ReportRoutes = () => {
  return (
    <Routes>
      <Route path={'/mis-dashboard'} element={<MisDashboardListController />} />
      <Route path={'/occupancy-revenue'} element={<OccupancyRevenueListController />} />
      <Route path={'/doctor-productivity'} element={<DoctorProductivityListController />} />
      <Route path={'/pharmacy-sales-analytics'} element={<PharmacySalesAnalyticsListController />} />
      <Route path={'/lab-revenue-analytics'} element={<LabRevenueAnalyticsListController />} />
      <Route path={'/attendance'} element={<AttendanceReportListController />} />
    </Routes>
  );
};

export default ReportRoutes;

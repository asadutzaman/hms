import React, {FC} from 'react'
import {Outlet, useNavigate, NavLink} from 'react-router-dom'
import {Button, Layout, Menu} from 'antd'
import {PatientPortalApi} from 'src/app/api'
import PatientStorageService from 'src/app/services/patientStorage.service'
import {PatientHttpService} from 'src/app/services/patientHttp.services'

const {Header, Content} = Layout
const StorageService = new PatientStorageService()

const navItems = [
  {key: '/patient-portal/dashboard', label: 'Dashboard'},
  {key: '/patient-portal/appointments', label: 'Appointments'},
  {key: '/patient-portal/prescriptions', label: 'Prescriptions'},
  {key: '/patient-portal/lab-reports', label: 'Lab Reports'},
  {key: '/patient-portal/bills', label: 'Bills'},
  {key: '/patient-portal/timeline', label: 'History'},
  {key: '/patient-portal/profile', label: 'Profile'},
]

const PatientPortalLayout: FC = () => {
  const navigate = useNavigate()

  const handleLogout = () => {
    PatientPortalApi.logout().finally(() => {
      StorageService.removeAccessToken()
      PatientHttpService.clearAccessToken()
      navigate('/patient-portal/login')
    })
  }

  return (
    <Layout style={{minHeight: '100vh'}}>
      <Header style={{display: 'flex', alignItems: 'center', background: '#fff', borderBottom: '1px solid #eee'}}>
        <div style={{fontWeight: 600, fontSize: 18, marginRight: 32}}>Patient Portal</div>
        <Menu
          mode='horizontal'
          style={{flex: 1, borderBottom: 'none'}}
          items={navItems.map((item) => ({
            key: item.key,
            label: <NavLink to={item.key}>{item.label}</NavLink>,
          }))}
        />
        <Button onClick={handleLogout}>Logout</Button>
      </Header>
      <Content style={{padding: 24}}>
        <Outlet />
      </Content>
    </Layout>
  )
}

export default PatientPortalLayout

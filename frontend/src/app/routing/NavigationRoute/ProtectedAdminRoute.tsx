import {FC, useContext, useEffect} from 'react'
import {useIdleTimer} from 'react-idle-timer'
import {Navigate, Outlet, useLocation, useNavigate} from 'react-router-dom'
import {AuthContext} from '../../context/auth/auth.context'

const ProtectedAdminRoute: FC<any> = ({
  layout: Layout,
  component: Component,
  permission,
  ...rest
}) => {
  const navigate = useNavigate()
  const location = useLocation()
  const {isAuthReady, isAuthenticated} = useContext(AuthContext)

  const hasPermission = false

  useEffect(() => {
    if (isAuthReady === true && isAuthenticated === false) {
      const redirectUrl = encodeURI(`${location.pathname}${location.search}`)
      localStorage.setItem('redirectUrl', redirectUrl)
      navigate(`/auth/login`)
    }
  }, [isAuthenticated])

  const handleOnIdle = () => {
    if (isAuthenticated === true) {
      // Perform logout or other actions
      navigate('/auth/logout')
    }
  }

  useIdleTimer({
    timeout: 1000 * 60 * 30, // (1000 * 1) = 1 seconds, (1000 * 60) = 1 minute, (1000 * 60 * 60) = 1 hour
    onIdle: handleOnIdle,
    debounce: 500, // Optional: smoothens event firing
  })

  if (permission && hasPermission) {
    return <Navigate to={`/admin/access/denied`} />
  }

  // Any authenticated staff user may enter the panel; feature access is
  // gated per-menu/per-route by scopes. Employees (doctors, nurses, etc.)
  // are not SERVICE_PROVIDER so isAdmin is false for them — gating on it
  // here blanked the whole panel for every non-admin staff member.
  if (isAuthenticated === true) {
    return <Outlet />
  }

  return null
}

export default ProtectedAdminRoute

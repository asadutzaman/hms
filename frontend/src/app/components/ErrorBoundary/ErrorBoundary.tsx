import React from 'react'
import {Alert} from 'antd'

interface Props {
  children: React.ReactNode
  // Optional label so the message can name what failed.
  section?: string
}

interface State {
  hasError: boolean
  message?: string
}

/**
 * Minimal error boundary. The app has no top-level boundary, so any render
 * error otherwise unmounts the whole tree and leaves a blank white screen.
 * Wrap risky/independently-rendered sections (e.g. a role-switched dashboard)
 * so a crash surfaces a readable message instead of blanking the page.
 */
class ErrorBoundary extends React.Component<Props, State> {
  constructor(props: Props) {
    super(props)
    this.state = {hasError: false}
  }

  static getDerivedStateFromError(error: any): State {
    return {hasError: true, message: error?.message || String(error)}
  }

  componentDidCatch(error: any, info: any) {
    // Keep the detail in the console for debugging; UI shows a summary.
    // eslint-disable-next-line no-console
    console.error('ErrorBoundary caught an error', error, info)
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className='p-6'>
          <Alert
            type='error'
            showIcon
            message={this.props.section ? `Failed to load ${this.props.section}` : 'Something went wrong'}
            description={this.state.message}
          />
        </div>
      )
    }
    return this.props.children
  }
}

export default ErrorBoundary

import { Component, ErrorInfo, ReactNode } from "react";

interface RenderError {
  (error?: Error, errorInfo?: ErrorInfo): ReactNode
}
interface ErrorProps {
  children: ReactNode;
  onError?: (error: Error, errorInfo: ErrorInfo) => void;
  fallback?: ReactNode | RenderError
}

interface ErrorState {
  failed: boolean;
  error?: Error;
  errorInfo?: ErrorInfo;
}

class ErrorBoundary extends Component<ErrorProps, ErrorState> {
  constructor(props: ErrorProps) {
    super(props);
    this.state = { failed: false };
  }
  static getDerivedStateFromError(error: Error): ErrorState {
    return { failed: true, error };
  }
  public componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    const { onError } = this.props;
    this.setState((prev) => ({
      ...prev,
      error: error ?? prev.error,
      errorInfo,
    }));
    console.error('Error');
    if (error) {
      console.error(error.message);
      console.debug(error.stack);
    }
    if (errorInfo) {
      console.debug(errorInfo.componentStack);
    }
    if (typeof onError !== "function") return;
    try {
      onError(error, errorInfo);
    } catch (e) {
      console.error(`Error calling onError`, e);
    }
  }
  public render() {
    const {
      fallback = "[ERROR]",
      children,
    } = this.props;

    const { failed, error, errorInfo } = this.state;

    let notice: ReactNode = null;

    if (failed) {
      if (typeof fallback === 'function') {
        try {
          notice = fallback(error, errorInfo);
        } catch (e) {
          console.error(`ErrorBoundary fallback`, e);
          notice = "[ERROR]";
        }
      } else {
        notice = fallback;
      }
    }
    return failed ? notice : children;
  }
}

export default ErrorBoundary;

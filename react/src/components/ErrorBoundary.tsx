import * as React from 'react';

interface ErrorBoundaryProps {
  children: React.ReactNode;
  fallback?: React.ComponentType<{ error: Error; retry: () => void }>;
  onError?: (error: Error, errorInfo: React.ErrorInfo) => void;
}

interface ErrorBoundaryState {
  hasError: boolean;
  error: Error | null;
}

const DefaultErrorFallback: React.FC<{ error: Error; retry: () => void }> = ({
  error,
  retry,
}) => {
  // Check if this is likely a deferred prop error
  const isDeferredError =
    error.message?.includes('Cannot read properties of undefined') ||
    error.message?.includes("reading 'search'") ||
    error.message?.includes("reading 'config'") ||
    error.message?.includes("reading 'pagination'");

  const errorTitle = isDeferredError
    ? 'Table data is loading...'
    : 'Something went wrong with the table';

  const errorMessage = isDeferredError
    ? 'The table data is still being loaded. If this persists, there may be an issue with the deferred data request.'
    : error.message || 'An unexpected error occurred';

  return (
    <div className="flex flex-col items-center justify-center p-8 border border-destructive/20 rounded-md bg-destructive/5">
      <div className="text-center space-y-4">
        <div className="text-destructive font-medium">{errorTitle}</div>
        <div className="text-sm text-muted-foreground max-w-md">
          {errorMessage}
        </div>
        {isDeferredError && (
          <div className="text-xs text-muted-foreground">
            💡 If using Inertia deferred props, ensure the table prop is
            properly set up on the server side.
          </div>
        )}
        <button
          onClick={retry}
          className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2"
        >
          {isDeferredError ? 'Reload table' : 'Try again'}
        </button>
      </div>
    </div>
  );
};

export class ErrorBoundary extends React.Component<
  ErrorBoundaryProps,
  ErrorBoundaryState
> {
  constructor(props: ErrorBoundaryProps) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
    this.props.onError?.(error, errorInfo);

    // Enhanced logging for development
    if (
      typeof process !== 'undefined' &&
      process.env?.NODE_ENV === 'development'
    ) {
      const isDeferredError = error.message?.includes(
        'Cannot read properties of undefined'
      );

      if (isDeferredError) {
        console.warn(
          '🔄 InertiaTable: Deferred prop error detected. This usually means the table data is still loading.',
          '\n💡 Consider using Inertia::defer() properly on the server side.',
          '\n📝 Error details:',
          error.message
        );
        console.error('Full error:', error, errorInfo);
      } else {
        console.error('Table ErrorBoundary caught an error:', error, errorInfo);
      }
    }
  }

  handleRetry = () => {
    this.setState({ hasError: false, error: null });
  };

  render() {
    if (this.state.hasError && this.state.error) {
      const FallbackComponent = this.props.fallback || DefaultErrorFallback;
      return (
        <FallbackComponent error={this.state.error} retry={this.handleRetry} />
      );
    }

    return this.props.children;
  }
}

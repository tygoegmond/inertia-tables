import * as React from "react";
interface ErrorBoundaryProps {
    children: React.ReactNode;
    fallback?: React.ComponentType<{
        error: Error;
        retry: () => void;
    }>;
    onError?: (error: Error, errorInfo: React.ErrorInfo) => void;
}
interface ErrorBoundaryState {
    hasError: boolean;
    error: Error | null;
}
export declare class ErrorBoundary extends React.Component<ErrorBoundaryProps, ErrorBoundaryState> {
    constructor(props: ErrorBoundaryProps);
    static getDerivedStateFromError(error: Error): ErrorBoundaryState;
    componentDidCatch(error: Error, errorInfo: React.ErrorInfo): void;
    handleRetry: () => void;
    render(): string | number | boolean | import("react/jsx-runtime").JSX.Element | Iterable<React.ReactNode> | null | undefined;
}
export {};

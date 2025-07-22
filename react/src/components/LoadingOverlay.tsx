import * as React from "react";

interface LoadingOverlayProps {
  isLoading?: boolean;
  className?: string;
}

export const LoadingOverlay = React.memo<LoadingOverlayProps>(({ 
  isLoading = false,
  className = ""
}) => {
  if (!isLoading) return null;

  return (
    <div 
      className={`absolute top-0 left-0 right-0 bottom-0 bg-white/70 dark:bg-black/70 flex items-center justify-center z-10 pointer-events-none rounded-md ${className}`}
      role="status"
      aria-live="polite"
      aria-label="Loading new data"
    >
      <div className="flex items-center gap-2 bg-white dark:bg-black px-3 py-2 rounded-lg shadow-sm border border-gray-200 dark:border-white/20 pointer-events-auto">
        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600 dark:border-white" />
        <span className="text-sm text-gray-600 dark:text-white">Loading...</span>
      </div>
    </div>
  );
});

LoadingOverlay.displayName = "LoadingOverlay";
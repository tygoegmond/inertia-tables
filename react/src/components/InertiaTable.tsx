import * as React from "react";
import { TableProps } from "../types";
import { DataTable } from "./DataTable";
import { TableSearch } from "./TableSearch";
import { TablePagination } from "./TablePagination";
import { ErrorBoundary } from "./ErrorBoundary";
import { DeferredTableLoader } from "./DeferredTableLoader";
import { useInertiaTable } from "../hooks";

const InertiaTableComponent = <T = any>({ 
  state, 
  className = "" 
}: TableProps<T>) => {
  const {
    searchValue,
    handleSearch,
    handleSort,
    handlePageChange,
    isNavigating,
  } = useInertiaTable({
    initialSearch: state?.search || '',
    tableState: state,
  });

  // Show loading state if data is deferred and not yet available
  if (!state) {
    return (
      <ErrorBoundary>
        <div 
          className={`space-y-4 ${className}`}
          role="region"
          aria-label="Loading data table"
        >
          <div className="max-w-sm">
            <div className="h-10 bg-gray-200 dark:bg-gray-700 rounded animate-pulse" />
          </div>
          <DeferredTableLoader />
          <div className="flex justify-between items-center">
            <div className="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse" />
            <div className="flex gap-2">
              {Array.from({ length: 3 }).map((_, i) => (
                <div key={i} className="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded animate-pulse" />
              ))}
            </div>
          </div>
        </div>
      </ErrorBoundary>
    );
  }

  return (
    <ErrorBoundary>
      <div 
        className={`space-y-4 ${className}`}
        role="region"
        aria-label="Interactive data table"
      >
        {state.config?.searchable && (
          <TableSearch
            value={searchValue}
            onChange={handleSearch}
            placeholder="Search..."
            className="max-w-sm"
          />
        )}

        <DataTable
          result={state}
          onSort={handleSort}
          isLoading={isNavigating}
        />

        {state.pagination && (
          <TablePagination
            pagination={state.pagination}
            onPageChange={handlePageChange}
          />
        )}
      </div>
    </ErrorBoundary>
  );
};

export const InertiaTable = React.memo(InertiaTableComponent) as typeof InertiaTableComponent;

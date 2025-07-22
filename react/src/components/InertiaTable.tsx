import * as React from "react";
import { TableProps } from "../types";
import { DataTable } from "./DataTable";
import { TableSearch } from "./TableSearch";
import { TablePagination } from "./TablePagination";
import { ErrorBoundary } from "./ErrorBoundary";
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
    initialSearch: state.search || '',
    tableState: state,
  });

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

        <TablePagination
          pagination={state.pagination}
          onPageChange={handlePageChange}
        />
      </div>
    </ErrorBoundary>
  );
};

export const InertiaTable = React.memo(InertiaTableComponent) as typeof InertiaTableComponent;

import * as React from "react";
import { TableProps } from "../types";
import { DataTable } from "./DataTable";
import { TableSearch } from "./TableSearch";
import { TablePagination } from "./TablePagination";
import { ErrorBoundary } from "./ErrorBoundary";
import { DeferredTableLoader } from "./DeferredTableLoader";
import { HeaderActions, BulkActions, ActionConfirmationDialog } from "./actions";
import { useInertiaTable, useTableActions, useActionDialog } from "../hooks";

const InertiaTableComponent = <T = any>({ 
  state, 
  className = "" 
}: TableProps<T>) => {
  const [selectedRecords, setSelectedRecords] = React.useState<T[]>([]);
  
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

  const {
    isLoading: isActionLoading,
    confirmationDialog,
    executeAction,
    executeBulkAction,
    executeHeaderAction,
    confirmAction,
    cancelAction,
  } = useTableActions({
    tableName: state?.name || 'table',
    onSuccess: (message) => {
      console.log('Action success:', message);
      setSelectedRecords([]); // Clear selection on success
    },
    onError: (error) => {
      console.error('Action error:', error);
    },
  });

  const handleRecordSelection = React.useCallback((records: T[]) => {
    setSelectedRecords(records);
  }, []);

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
        {/* Header section with search and header actions */}
        <div className="flex items-center justify-between gap-4">
          <div className="flex-1">
            {state.config?.searchable && (
              <TableSearch
                value={searchValue}
                onChange={handleSearch}
                placeholder="Search..."
                className="max-w-sm"
              />
            )}
          </div>
          
          {state.headerActions && state.headerActions.length > 0 && (
            <HeaderActions
              headerActions={state.headerActions}
              onActionClick={executeHeaderAction}
            />
          )}
        </div>

        <DataTable
          result={state}
          onSort={handleSort}
          isLoading={isNavigating || isActionLoading}
          onRecordSelect={handleRecordSelection}
          onActionClick={executeAction}
        />

        {/* Bulk actions */}
        {state.bulkActions && state.bulkActions.length > 0 && (
          <BulkActions
            bulkActions={state.bulkActions}
            selectedRecords={selectedRecords}
            onBulkActionClick={executeBulkAction}
          />
        )}

        {state.pagination && (
          <TablePagination
            pagination={state.pagination}
            onPageChange={handlePageChange}
          />
        )}

        {/* Action confirmation dialog */}
        <ActionConfirmationDialog
          open={confirmationDialog.isOpen}
          onOpenChange={(open) => !open && cancelAction()}
          title={confirmationDialog.title}
          message={confirmationDialog.message}
          confirmButton={confirmationDialog.confirmButton}
          cancelButton={confirmationDialog.cancelButton}
          onConfirm={confirmAction}
          onCancel={cancelAction}
          isLoading={isActionLoading}
        />
      </div>
    </ErrorBoundary>
  );
};

export const InertiaTable = React.memo(InertiaTableComponent) as typeof InertiaTableComponent;

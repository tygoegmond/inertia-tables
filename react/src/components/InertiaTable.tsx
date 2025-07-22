import * as React from "react";
import { TableProps } from "../types";
import { DataTable } from "./DataTable";
import { TablePagination } from "./TablePagination";
import { ErrorBoundary } from "./ErrorBoundary";
import { DeferredTableLoader } from "./DeferredTableLoader";
import { BulkActions, ActionConfirmationDialog } from "./actions";
import { useInertiaTable, useTableActions } from "../hooks";

const InertiaTableComponent = <T extends Record<string, any> = Record<string, any>>({ 
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
          className={`flex flex-col gap-4 ${className}`}
          role="region"
          aria-label="Loading data table"
        >
          <div className="max-w-sm">
            <div className="h-10 bg-muted rounded animate-pulse" />
          </div>
          <DeferredTableLoader />
          <div className="flex justify-between items-center">
            <div className="h-4 w-32 bg-muted rounded animate-pulse" />
            <div className="flex gap-2">
              {Array.from({ length: 3 }).map((_, i) => (
                <div key={i} className="h-10 w-10 bg-muted rounded animate-pulse" />
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
        className={`flex flex-col gap-4 ${className}`}
        role="region"
        aria-label="Interactive data table"
      >
        <DataTable
          result={state}
          onSort={handleSort}
          isLoading={isNavigating || isActionLoading}
          onRecordSelect={handleRecordSelection}
          onActionClick={executeAction}
          searchValue={searchValue}
          onSearch={handleSearch}
          onHeaderActionClick={executeHeaderAction}
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

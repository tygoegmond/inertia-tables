import * as React from 'react';
import { useInertiaTable, useTableActions } from '../hooks';
import { TableProps } from '../types';
import { DataTable } from './DataTable';
import { DeferredTableLoader } from './DeferredTableLoader';
import { ErrorBoundary } from './ErrorBoundary';
import { TablePagination } from './TablePagination';
import { ActionConfirmationDialog } from './actions';

const InertiaTableComponent = <T extends Record<string, any> = Record<string, any>>({ state, className = '' }: TableProps<T>) => {
    const [selectedRecords, setSelectedRecords] = React.useState<T[]>([]);

    const { searchValue, handleSearch, handleSort, handlePageChange, isNavigating } = useInertiaTable({
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
        primaryKey: state?.primaryKey || 'id',
        onSuccess: (message) => {
            // TODO: Implement user-facing success notification
            setSelectedRecords([]);
        },
        onError: (error) => {
            // TODO: Implement user-facing error notification
        },
    });

    const handleRecordSelection = React.useCallback((records: T[]) => {
        setSelectedRecords(records);
    }, []);

    // Show loading state if data is deferred and not yet available
    if (!state) {
        return (
            <ErrorBoundary>
                <div className={`flex flex-col gap-4 ${className}`} role="region" aria-label="Loading data table">
                    <div className="max-w-sm">
                        <div className="h-10 animate-pulse rounded bg-muted" />
                    </div>
                    <DeferredTableLoader />
                    <div className="flex items-center justify-between">
                        <div className="h-4 w-32 animate-pulse rounded bg-muted" />
                        <div className="flex gap-2">
                            {Array.from({ length: 3 }).map((_, i) => (
                                <div key={i} className="h-10 w-10 animate-pulse rounded bg-muted" />
                            ))}
                        </div>
                    </div>
                </div>
            </ErrorBoundary>
        );
    }

    return (
        <ErrorBoundary>
            <div className={`flex flex-col gap-4 ${className}`} role="region" aria-label="Interactive data table">
                <DataTable
                    result={state}
                    onSort={handleSort}
                    isLoading={isNavigating || isActionLoading}
                    onRecordSelect={handleRecordSelection}
                    onActionClick={executeAction}
                    searchValue={searchValue}
                    onSearch={handleSearch}
                    onHeaderActionClick={executeHeaderAction}
                    onBulkActionClick={executeBulkAction}
                />


                {state.pagination && <TablePagination pagination={state.pagination} onPageChange={handlePageChange} />}

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
                    variant={confirmationDialog.variant}
                />
            </div>
        </ErrorBoundary>
    );
};

export const InertiaTable = React.memo(InertiaTableComponent) as typeof InertiaTableComponent;

import * as React from "react";
import {
  useReactTable,
  getCoreRowModel,
  getSortedRowModel,
  RowSelectionState,
} from "@tanstack/react-table";
import { Table } from "./ui/table";
import { TableResult, TableColumn } from "../types";
import { TextColumn } from "./columns";
import { useTableState, useTableColumns } from "../hooks";
import { ErrorBoundary } from "./ErrorBoundary";
import { TableHeaderComponent } from "./table/TableHeaderComponent";
import { TableBodyComponent } from "./table/TableBodyComponent";
import { LoadingOverlay } from "./LoadingOverlay";

interface DataTableProps<T = any> {
  result: TableResult<T> | undefined;
  onSort?: (column: string, direction: 'asc' | 'desc') => void;
  className?: string;
  isLoading?: boolean;
  emptyMessage?: string;
  onRecordSelect?: (records: T[]) => void;
  onActionClick?: (action: any, record?: Record<string, any>) => void;
}

function renderColumnValue(column: TableColumn, value: any, record: any) {
  return <TextColumn column={column} value={value} record={record} />;
}

export const DataTable = React.memo<DataTableProps>(({ 
  result, 
  onSort, 
  className = "",
  isLoading = false,
  emptyMessage = "No results.",
  onRecordSelect,
  onActionClick
}) => {
  // Handle deferred/undefined result
  if (!result) {
    return (
      <div className={`rounded-md border ${className}`}>
        <div className="flex items-center justify-center p-8">
          <div className="flex items-center gap-2">
            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600 dark:border-white" />
            <span className="text-sm text-gray-600 dark:text-gray-300">Loading table...</span>
          </div>
        </div>
      </div>
    );
  }

  const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});

  const { sorting, setSorting, handleSort, error: stateError } = useTableState({ 
    result, 
    onSort 
  });
  
  const { columns, error: columnsError } = useTableColumns({
    result,
    renderCell: renderColumnValue,
    onRecordSelect,
    onActionClick,
  });

  const error = stateError || columnsError;

  const table = useReactTable({
    data: result.data || [],
    columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    enableRowSelection: true,
    state: {
      sorting,
      rowSelection,
    },
    onSortingChange: setSorting,
    onRowSelectionChange: setRowSelection,
    manualSorting: true,
    getRowId: (row, index) => {
      // Use the primary key field specified by the backend, or fallback to 'id', then index
      const primaryKeyField = result.primaryKey || 'id';
      return row[primaryKeyField]?.toString() || index.toString();
    },
  });

  // Notify parent of selection changes
  React.useEffect(() => {
    if (onRecordSelect) {
      const selectedRows = table.getFilteredSelectedRowModel().rows.map(row => row.original);
      onRecordSelect(selectedRows);
    }
  }, [rowSelection, table, onRecordSelect]);

  if (error) {
    throw error;
  }

  return (
    <ErrorBoundary>
      <div className={`rounded-md border ${className}`}>
        <div 
          role="table"
          aria-label="Data table"
          aria-rowcount={result.data?.length || 0}
          className="relative"
        >
          <Table>
            <TableHeaderComponent
              headerGroups={table.getHeaderGroups()}
              result={result}
              onSort={handleSort}
            />
            <TableBodyComponent
              rows={table.getRowModel().rows}
              columnsCount={columns.length}
              emptyMessage={emptyMessage}
            />
          </Table>
          <LoadingOverlay isLoading={isLoading} />
        </div>
      </div>
    </ErrorBoundary>
  );
});

DataTable.displayName = "DataTable";

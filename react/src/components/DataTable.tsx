import * as React from "react";
import {
  useReactTable,
  getCoreRowModel,
  getSortedRowModel,
} from "@tanstack/react-table";
import { Table } from "./ui/table";
import { TableResult, TableColumn } from "../types";
import { TextColumn } from "./columns";
import { useTableState, useTableColumns } from "../hooks";
import { ErrorBoundary } from "./ErrorBoundary";
import { TableHeaderComponent } from "./table/TableHeaderComponent";
import { TableBodyComponent } from "./table/TableBodyComponent";
import { LoadingOverlay } from "./LoadingOverlay";

interface DataTableProps {
  result: TableResult;
  onSort?: (column: string, direction: 'asc' | 'desc') => void;
  className?: string;
  isLoading?: boolean;
  emptyMessage?: string;
}

function renderColumnValue(column: TableColumn, value: any, record: any) {
  return <TextColumn column={column} value={value} record={record} />;
}

export const DataTable = React.memo<DataTableProps>(({ 
  result, 
  onSort, 
  className = "",
  isLoading = false,
  emptyMessage = "No results."
}) => {
  const { sorting, setSorting, handleSort, error: stateError } = useTableState({ 
    result, 
    onSort 
  });
  
  const { columns, error: columnsError } = useTableColumns({
    result,
    renderCell: renderColumnValue,
  });

  const error = stateError || columnsError;

  const table = useReactTable({
    data: result.data || [],
    columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    state: {
      sorting,
    },
    onSortingChange: setSorting,
    manualSorting: true,
  });

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
            <LoadingOverlay isLoading={isLoading} />  
          </Table>
        </div>
      </div>
    </ErrorBoundary>
  );
});

DataTable.displayName = "DataTable";

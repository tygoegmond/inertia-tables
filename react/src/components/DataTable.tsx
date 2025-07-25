import * as React from 'react';
import {
  useReactTable,
  getCoreRowModel,
  getSortedRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getFacetedRowModel,
  getFacetedUniqueValues,
  RowSelectionState,
  ColumnFiltersState,
  SortingState,
  VisibilityState,
  flexRender,
} from '@tanstack/react-table';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from './ui/table';
import { Checkbox } from './ui/checkbox';
import { TableResult, TableColumn } from '../types';
import { TextColumn } from './columns';
import { useTableState } from '../hooks';
import { ErrorBoundary } from './ErrorBoundary';
import { LoadingOverlay } from './LoadingOverlay';
import { DataTableColumnHeader } from './data-table/data-table-column-header';
import { DataTableRowActions } from './data-table/data-table-row-actions';
// import { DataTableToolbar } from './data-table/data-table-toolbar';
import { DataTableViewOptions } from './data-table/data-table-view-options';
import { DataTableBulkActions } from './data-table/data-table-bulk-actions';
import { Input } from './ui/input';
import { HeaderActions } from './actions';

interface DataTableProps<T = any> {
  result: TableResult<T> | undefined;
  onSort?: (column: string | null, direction: 'asc' | 'desc' | null) => void;
  className?: string;
  isLoading?: boolean;
  emptyMessage?: string;
  onActionClick?: (action: any, record?: Record<string, any>) => void;
  searchValue?: string;
  onSearch?: (value: string) => void;
  onHeaderActionClick?: (action: any) => void;
  onBulkActionClick?: (action: any, records: Record<string, unknown>[]) => void;
}

function renderColumnValue(column: TableColumn, value: any, record: any) {
  return <TextColumn column={column} value={value} record={record} />;
}

export const DataTable = React.memo<DataTableProps>(
  ({
    result,
    onSort,
    className = '',
    isLoading = false,
    emptyMessage = 'No results.',
    onActionClick,
    searchValue,
    onSearch,
    onHeaderActionClick,
    onBulkActionClick,
  }) => {
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>(
      {}
    );
    const [columnVisibility, setColumnVisibility] =
      React.useState<VisibilityState>({});
    const [columnFilters, setColumnFilters] =
      React.useState<ColumnFiltersState>([]);
    const [sorting, setSorting] = React.useState<SortingState>([]);

    const { error: stateError } = useTableState({
      result,
      onSort,
    });

    const tableColumns = React.useMemo(() => {
      if (!result?.config?.columns) return [];

      const columns: any[] = [];

      // Add selection column if we have selectable records or bulk actions
      if (
        result.config.selectable ||
        (result.bulkActions && result.bulkActions.length > 0)
      ) {
        columns.push({
          id: 'select',
          header: ({ table }: any) => (
            <Checkbox
              checked={
                table.getIsAllPageRowsSelected() ||
                (table.getIsSomePageRowsSelected() && 'indeterminate')
              }
              onCheckedChange={(value: boolean) =>
                table.toggleAllPageRowsSelected(!!value)
              }
              aria-label="Select all"
            />
          ),
          cell: ({ row }: any) => (
            <Checkbox
              checked={row.getIsSelected()}
              onCheckedChange={(value: boolean) => row.toggleSelected(!!value)}
              aria-label="Select row"
            />
          ),
          enableSorting: false,
          enableHiding: false,
        });
      }

      // Add data columns
      result.config.columns.forEach((column: TableColumn) => {
        columns.push({
          id: column.key,
          accessorKey: column.key,
          header: ({ column: tableColumn }: any) => (
            <DataTableColumnHeader
              column={tableColumn}
              title={column.label || column.key}
            />
          ),
          cell: ({ row, getValue }: any) => {
            const value = getValue();
            return renderColumnValue(column, value, row.original);
          },
          enableSorting: column.sortable ?? false,
        });
      });

      // Add actions column if we have row actions
      if (result.actions && result.actions.length > 0) {
        columns.push({
          id: 'actions',
          header: '',
          cell: ({ row }: any) => (
            <DataTableRowActions
              row={row}
              staticActions={result.actions || []}
              onActionClick={(action, record) =>
                onActionClick?.(action, record)
              }
            />
          ),
          enableSorting: false,
          enableHiding: false,
        });
      }

      return columns;
    }, [result, onActionClick]);

    const error = stateError;

    const table = useReactTable({
      data: result?.data || [],
      columns: tableColumns,
      state: {
        sorting,
        columnVisibility,
        rowSelection,
        columnFilters,
      },
      enableRowSelection: true,
      onRowSelectionChange: setRowSelection,
      onSortingChange: (updater) => {
        const newSorting =
          typeof updater === 'function' ? updater(sorting) : updater;
        setSorting(newSorting);

        // Handle server-side sorting
        if (onSort) {
          if (newSorting.length > 0) {
            const { id, desc } = newSorting[0];
            onSort(id, desc ? 'desc' : 'asc');
          } else {
            // Clear sorting on the server
            onSort(null, null);
          }
        }
      },
      onColumnFiltersChange: setColumnFilters,
      onColumnVisibilityChange: setColumnVisibility,
      getCoreRowModel: getCoreRowModel(),
      getFilteredRowModel: getFilteredRowModel(),
      getPaginationRowModel: getPaginationRowModel(),
      getSortedRowModel: getSortedRowModel(),
      getFacetedRowModel: getFacetedRowModel(),
      getFacetedUniqueValues: getFacetedUniqueValues(),
      manualSorting: !!onSort, // Use manual sorting when onSort is provided
      getRowId: (row, index) => {
        // Use the primary key field specified by the backend, or fallback to 'id', then index
        const primaryKeyField = result?.primaryKey || 'id';
        return row[primaryKeyField]?.toString() || index.toString();
      },
    });

    // Track selected records for bulk actions
    const selectedRecords = React.useMemo(() => {
      return table
        .getFilteredSelectedRowModel()
        .rows.map((row) => row.original);
    }, [rowSelection, table]);

    if (error) {
      throw error;
    }

    return (
      <ErrorBoundary>
        <div className="flex flex-col gap-4">
          {/* Enhanced Toolbar */}
          {((result?.config?.searchable ?? false) ||
            result?.headerActions?.length) && (
            <div className="flex items-center justify-between gap-4">
              <div className="flex-1">
                {(result?.config?.searchable ?? false) && onSearch && (
                  <Input
                    placeholder="Search..."
                    value={searchValue || ''}
                    onChange={(e) => onSearch(e.target.value)}
                    className="h-8 w-[150px] lg:w-[250px]"
                  />
                )}
              </div>

              <div className="flex items-center gap-2">
                {result?.bulkActions &&
                  result?.bulkActions.length > 0 &&
                  onBulkActionClick && (
                    <DataTableBulkActions
                      bulkActions={result.bulkActions}
                      selectedRecords={selectedRecords}
                      onBulkActionClick={onBulkActionClick}
                    />
                  )}
                <DataTableViewOptions table={table} />
                {result?.headerActions &&
                  result?.headerActions.length > 0 &&
                  onHeaderActionClick && (
                    <HeaderActions
                      headerActions={result?.headerActions || []}
                      onActionClick={onHeaderActionClick}
                    />
                  )}
              </div>
            </div>
          )}

          <div className={`rounded-md border ${className}`}>
            <div
              role="table"
              aria-label="Data table"
              aria-rowcount={result?.data?.length || 0}
              className="relative"
            >
              <Table>
                <TableHeader>
                  {table.getHeaderGroups().map((headerGroup) => (
                    <TableRow key={headerGroup.id}>
                      {headerGroup.headers.map((header) => {
                        return (
                          <TableHead key={header.id} colSpan={header.colSpan}>
                            {header.isPlaceholder
                              ? null
                              : flexRender(
                                  header.column.columnDef.header,
                                  header.getContext()
                                )}
                          </TableHead>
                        );
                      })}
                    </TableRow>
                  ))}
                </TableHeader>
                <TableBody>
                  {table.getRowModel().rows?.length ? (
                    table.getRowModel().rows.map((row) => (
                      <TableRow
                        key={row.id}
                        data-state={row.getIsSelected() && 'selected'}
                      >
                        {row.getVisibleCells().map((cell) => (
                          <TableCell key={cell.id}>
                            {flexRender(
                              cell.column.columnDef.cell,
                              cell.getContext()
                            )}
                          </TableCell>
                        ))}
                      </TableRow>
                    ))
                  ) : (
                    <TableRow>
                      <TableCell
                        colSpan={tableColumns.length}
                        className="h-24 text-center"
                      >
                        {emptyMessage}
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
              <LoadingOverlay isLoading={isLoading} />
            </div>
          </div>
        </div>
      </ErrorBoundary>
    );
  }
);

DataTable.displayName = 'DataTable';

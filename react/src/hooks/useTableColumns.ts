import * as React from "react";
import { ColumnDef } from "@tanstack/react-table";
import { TableResult, TableColumn, TableAction } from "../types";
import { ActionsColumn } from "../components/actions/ActionsColumn";

interface UseTableColumnsProps {
  result: TableResult | undefined;
  renderCell?: (column: TableColumn, value: any, record: any) => React.ReactNode;
  onRecordSelect?: (records: any[]) => void;
  onActionClick?: (action: TableAction, record?: Record<string, any>) => void;
}

interface TableColumnsState {
  columns: ColumnDef<any>[];
  visibleColumns: TableColumn[];
  error: Error | null;
}

export function useTableColumns({
  result,
  renderCell,
  onRecordSelect,
  onActionClick,
}: UseTableColumnsProps): TableColumnsState {
  const [error, setError] = React.useState<Error | null>(null);
  const [selectedRows, setSelectedRows] = React.useState<Set<string>>(new Set());

  const { columns, visibleColumns } = React.useMemo(() => {
    try {
      setError(null);
      
      // Return empty arrays if result is undefined (deferred)
      if (!result) {
        return { columns: [], visibleColumns: [] };
      }
      
      const configColumns = result.config?.columns || [];
      
      if (!Array.isArray(configColumns)) {
        throw new Error('Table columns configuration is invalid');
      }

      const visibleColumns = configColumns.filter(column => column.visible);
      const columns: ColumnDef<any>[] = [];

      // Add selection column if bulk actions exist
      if (result.bulkActions && result.bulkActions.length > 0) {
        columns.push({
          id: 'select',
          header: ({ table }) => {
            const allPageRowsSelected = table.getIsAllPageRowsSelected();
            const someRowsSelected = table.getIsSomeRowsSelected();
            
            return React.createElement('input', {
              type: 'checkbox',
              checked: allPageRowsSelected,
              ref: (el: HTMLInputElement | null) => {
                if (el) el.indeterminate = someRowsSelected && !allPageRowsSelected;
              },
              onChange: table.getToggleAllPageRowsSelectedHandler(),
              className: 'rounded border-gray-300 text-blue-600 focus:ring-blue-500',
            });
          },
          cell: ({ row }) => React.createElement('input', {
            type: 'checkbox',
            checked: row.getIsSelected(),
            onChange: row.getToggleSelectedHandler(),
            className: 'rounded border-gray-300 text-blue-600 focus:ring-blue-500',
          }),
          size: 40,
          enableSorting: false,
        });
      }

      // Add data columns
      visibleColumns.forEach(column => {
        columns.push({
          id: column.key,
          accessorFn: (row) => row[column.key],
          header: column.label,
          cell: ({ row }) => {
            const value = row.getValue(column.key);
            return renderCell ? renderCell(column, value, row.original) : value;
          },
          enableSorting: column.sortable,
          meta: {
            column,
          },
        });
      });

      // Add actions column if row actions exist
      if (result.actions && result.actions.length > 0 && onActionClick) {
        columns.push({
          id: 'actions',
          header: 'Actions',
          cell: ({ row }) => {
            return React.createElement(ActionsColumn, {
              staticActions: result.actions || [],
              record: row.original,
              onActionClick,
            });
          },
          size: 100,
          enableSorting: false,
        });
      }

      return { columns, visibleColumns };
    } catch (err) {
      const error = err instanceof Error ? err : new Error('Failed to process table columns');
      setError(error);
      return { columns: [], visibleColumns: [] };
    }
  }, [result?.config?.columns, result?.actions, result?.bulkActions, renderCell, onRecordSelect, onActionClick]);

  return {
    columns,
    visibleColumns,
    error,
  };
}
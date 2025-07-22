import * as React from "react";
import { ColumnDef } from "@tanstack/react-table";
import { TableResult, TableColumn } from "../types";

interface UseTableColumnsProps {
  result: TableResult;
  renderCell?: (column: TableColumn, value: any, record: any) => React.ReactNode;
}

interface TableColumnsState {
  columns: ColumnDef<any>[];
  visibleColumns: TableColumn[];
  error: Error | null;
}

export function useTableColumns({
  result,
  renderCell,
}: UseTableColumnsProps): TableColumnsState {
  const [error, setError] = React.useState<Error | null>(null);

  const { columns, visibleColumns } = React.useMemo(() => {
    try {
      setError(null);
      
      const configColumns = result.config?.columns || [];
      
      if (!Array.isArray(configColumns)) {
        throw new Error('Table columns configuration is invalid');
      }

      const visibleColumns = configColumns.filter(column => column.visible);
      
      const columns: ColumnDef<any>[] = visibleColumns.map((column): ColumnDef<any> => ({
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
      }));

      return { columns, visibleColumns };
    } catch (err) {
      const error = err instanceof Error ? err : new Error('Failed to process table columns');
      setError(error);
      return { columns: [], visibleColumns: [] };
    }
  }, [result.config?.columns, renderCell]);

  return {
    columns,
    visibleColumns,
    error,
  };
}
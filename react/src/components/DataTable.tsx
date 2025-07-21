import * as React from "react";
import {
  useReactTable,
  getCoreRowModel,
  getSortedRowModel,
  ColumnDef,
  flexRender,
  SortingState,
} from "@tanstack/react-table";
import { ChevronUp, ChevronDown } from "lucide-react";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "./ui/table";
import { TableProps, TableColumn } from "../types";
import { TextColumn } from "./columns";

function renderColumnValue(column: TableColumn, value: any, record: any) {
  return <TextColumn column={column} value={value} record={record} />;
}

export function DataTable({ result, onSort, className }: TableProps) {
  const [sorting, setSorting] = React.useState<SortingState>([]);

  const columns: ColumnDef<any>[] = React.useMemo(() => {
    const configColumns = result.config?.columns || [];
    if (!Array.isArray(configColumns)) {
      console.error('configColumns is not an array:', configColumns);
      return [];
    }
    return configColumns
      .filter(column => column.visible)
      .map((column): ColumnDef<any> => ({
        id: column.key,
        accessorKey: column.key,
        header: ({ column: tanstackColumn }) => {
          const isActive = Object.keys(result.sort || {}).includes(column.key);
          const direction = result.sort?.[column.key];
          
          return (
            <div
              className={`flex items-center gap-2 ${
                column.sortable ? 'cursor-pointer select-none hover:text-foreground' : ''
              }`}
              onClick={() => {
                if (column.sortable && onSort) {
                  const newDirection = direction === 'asc' ? 'desc' : 'asc';
                  onSort(column.key, newDirection);
                }
              }}
            >
              {column.label}
              {column.sortable && (
                <div className="flex flex-col">
                  <ChevronUp 
                    className={`h-3 w-3 ${
                      isActive && direction === 'asc' ? 'text-foreground' : 'text-muted-foreground'
                    }`} 
                  />
                  <ChevronDown 
                    className={`h-3 w-3 -mt-1 ${
                      isActive && direction === 'desc' ? 'text-foreground' : 'text-muted-foreground'
                    }`} 
                  />
                </div>
              )}
            </div>
          );
        },
        cell: ({ row }) => {
          const value = row.getValue(column.key);
          return renderColumnValue(column, value, row.original);
        },
        enableSorting: column.sortable,
      }));
  }, [result.config?.columns, result.sort, onSort]);

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

  return (
    <div className={className}>
      <Table>
        <TableHeader>
          {table.getHeaderGroups().map((headerGroup) => (
            <TableRow key={headerGroup.id}>
              {headerGroup.headers.map((header) => (
                <TableHead key={header.id}>
                  {header.isPlaceholder
                    ? null
                    : flexRender(
                        header.column.columnDef.header,
                        header.getContext()
                      )}
                </TableHead>
              ))}
            </TableRow>
          ))}
        </TableHeader>
        <TableBody>
          {table.getRowModel().rows?.length ? (
            table.getRowModel().rows.map((row) => (
              <TableRow
                key={row.id}
                data-state={row.getIsSelected() && "selected"}
              >
                {row.getVisibleCells().map((cell) => (
                  <TableCell key={cell.id}>
                    {flexRender(cell.column.columnDef.cell, cell.getContext())}
                  </TableCell>
                ))}
              </TableRow>
            ))
          ) : (
            <TableRow>
              <TableCell colSpan={columns.length} className="h-24 text-center">
                No results.
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
    </div>
  );
}
import * as React from "react";
import { flexRender, HeaderGroup } from "@tanstack/react-table";
import { TableHeader, TableHead, TableRow } from "../ui/table";
import { SortableHeader } from "./SortableHeader";
import { TableColumn, TableResult } from "../../types";

interface TableHeaderComponentProps<T = unknown> {
  headerGroups: HeaderGroup<T>[];
  result: TableResult<T>;
  onSort?: (column: string, direction: 'asc' | 'desc') => void;
}

export const TableHeaderComponent = React.memo<TableHeaderComponentProps>(({
  headerGroups,
  result,
  onSort,
}) => {
  return (
    <TableHeader>
      {headerGroups.map((headerGroup) => (
        <TableRow key={headerGroup.id}>
          {headerGroup.headers.map((header) => {
            const column = (header.column.columnDef.meta as { column?: TableColumn })?.column;
            const sortDirection = column?.key ? result.sort?.[column.key] || null : null;

            return (
              <TableHead key={header.id}>
                {header.isPlaceholder ? null : column ? (
                  <SortableHeader
                    column={column}
                    sortDirection={sortDirection}
                    onSort={onSort}
                  />
                ) : (
                  flexRender(header.column.columnDef.header, header.getContext())
                )}
              </TableHead>
            );
          })}
        </TableRow>
      ))}
    </TableHeader>
  );
});

TableHeaderComponent.displayName = "TableHeaderComponent";
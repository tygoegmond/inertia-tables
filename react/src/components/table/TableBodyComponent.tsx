import * as React from 'react';
import { flexRender, Row } from '@tanstack/react-table';
import { TableBody, TableCell, TableRow } from '../ui/table';

interface TableBodyComponentProps {
  rows: Row<any>[];
  columnsCount: number;
  emptyMessage?: string;
}

export const TableBodyComponent = React.memo<TableBodyComponentProps>(
  ({ rows, columnsCount, emptyMessage = 'No results.' }) => {
    if (!rows?.length) {
      return (
        <TableBody>
          <TableRow>
            <TableCell
              colSpan={columnsCount}
              className="h-24 text-center"
              role="status"
              aria-live="polite"
            >
              {emptyMessage}
            </TableCell>
          </TableRow>
        </TableBody>
      );
    }

    return (
      <TableBody>
        {rows.map((row) => (
          <TableRow
            key={row.id}
            data-state={row.getIsSelected() && 'selected'}
            role="row"
          >
            {row.getVisibleCells().map((cell) => (
              <TableCell key={cell.id} role="gridcell">
                {flexRender(cell.column.columnDef.cell, cell.getContext())}
              </TableCell>
            ))}
          </TableRow>
        ))}
      </TableBody>
    );
  }
);

TableBodyComponent.displayName = 'TableBodyComponent';

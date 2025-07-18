import * as React from "react";
import { TableColumn } from "../../types";

interface TextColumnProps {
  column: TableColumn;
  value: any;
  record: any;
}

export function TextColumn({ column, value, record }: TextColumnProps) {
  if (value === null || value === undefined) {
    return <span className="text-muted-foreground">—</span>;
  }

  let formatted = String(value);

  if (column.limit && formatted.length > column.limit) {
    formatted = formatted.substring(0, column.limit) + '...';
  }

  if (column.prefix) {
    formatted = column.prefix + formatted;
  }

  if (column.suffix) {
    formatted = formatted + column.suffix;
  }

  const wrapClass = column.wrap === 'truncate' ? 'truncate' : 'break-words';

  return (
    <div className={`${wrapClass} ${column.copyable ? 'cursor-pointer select-all' : ''}`}>
      {formatted}
    </div>
  );
}
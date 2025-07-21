import * as React from "react";
import { TableColumn } from "../../types";
import { Badge } from "../ui/badge";

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

  // If badge is enabled, render as Badge component
  if (column.badge) {
    const variant = (column.badgeVariant as "default" | "secondary" | "destructive" | "outline") || "default";
    
    return (
      <Badge 
        variant={variant}
        className={column.copyable ? 'cursor-pointer select-all' : ''}
      >
        {formatted}
      </Badge>
    );
  }

  // Otherwise render as normal text
  return (
    <div className={`${wrapClass} ${column.copyable ? 'cursor-pointer select-all' : ''}`}>
      {formatted}
    </div>
  );
}
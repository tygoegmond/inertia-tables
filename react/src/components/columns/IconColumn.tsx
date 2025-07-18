import * as React from "react";
import { TableColumn } from "../../types";

interface IconColumnProps {
  column: TableColumn;
  value: any;
  record: any;
}

export function IconColumn({ column, value, record }: IconColumnProps) {
  if (value === null || value === undefined) {
    return <span className="text-muted-foreground">—</span>;
  }

  const size = column.size || 16;
  const iconName = column.icon || String(value);

  return (
    <div className="flex items-center justify-center">
      <span 
        className="text-muted-foreground" 
        style={{ fontSize: `${size}px` }}
        title={String(value)}
      >
        {iconName}
      </span>
    </div>
  );
}
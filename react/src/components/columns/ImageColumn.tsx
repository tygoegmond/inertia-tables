import * as React from "react";
import { TableColumn } from "../../types";

interface ImageColumnProps {
  column: TableColumn;
  value: any;
  record: any;
}

export function ImageColumn({ column, value, record }: ImageColumnProps) {
  if (value === null || value === undefined) {
    return <span className="text-muted-foreground">—</span>;
  }

  const size = column.size || 32;
  const rounded = column.rounded ? 'rounded-full' : 'rounded';

  return (
    <div className="flex items-center justify-center">
      <img
        src={String(value)}
        alt=""
        className={`${rounded} object-cover`}
        style={{ width: `${size}px`, height: `${size}px` }}
        onError={(e) => {
          const target = e.target as HTMLImageElement;
          target.style.display = 'none';
        }}
      />
    </div>
  );
}
import * as React from "react";
import { TableColumn } from "../../types";

interface ActionColumnProps {
  column: TableColumn;
  value: any;
  record: any;
}

export function ActionColumn({ column, value, record }: ActionColumnProps) {
  if (!column.actions || column.actions.length === 0) {
    return null;
  }

  return (
    <div className="flex items-center gap-2">
      {column.actions.map((action, index) => (
        <button
          key={index}
          onClick={() => action.onClick(record)}
          className="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded hover:bg-muted transition-colors"
          title={action.label}
        >
          {action.icon && <span>{action.icon}</span>}
          {action.label}
        </button>
      ))}
    </div>
  );
}
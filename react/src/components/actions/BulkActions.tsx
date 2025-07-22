import * as React from "react";
import { ActionButton } from "./ActionButton";
import { Badge } from "../ui/badge";
import type { TableBulkAction } from "../../types";

interface BulkActionsProps {
  bulkActions: TableBulkAction[];
  selectedRecords: Record<string, any>[];
  onBulkActionClick: (action: TableBulkAction, records: Record<string, any>[]) => void;
  className?: string;
}

export const BulkActions: React.FC<BulkActionsProps> = ({
  bulkActions,
  selectedRecords,
  onBulkActionClick,
  className = "",
}) => {
  const selectedCount = selectedRecords.length;

  // Don't render if no records are selected
  if (selectedCount === 0) {
    return null;
  }

  // Filter out hidden actions
  const visibleActions = bulkActions.filter(action => !action.hidden);

  if (visibleActions.length === 0) {
    return null;
  }

  return (
    <div className={`flex items-center gap-4 p-3 bg-accent/50 border-t ${className}`}>
      <div className="flex items-center gap-2">
        <Badge variant="secondary" className="text-xs">
          {selectedCount} selected
        </Badge>
        
        <div className="h-4 w-px bg-border" />
        
        <div className="flex items-center gap-2">
          {visibleActions.map((action) => (
            <ActionButton
              key={action.name}
              action={action}
              onClick={() => onBulkActionClick(action, selectedRecords)}
              disabled={action.disabled}
            />
          ))}
        </div>
      </div>
    </div>
  );
};
import * as React from "react";
import { Button } from "../ui/button";
import type { TableAction } from "../../types";

interface HeaderActionsProps {
  headerActions: TableAction[];
  onActionClick: (action: TableAction) => void;
  className?: string;
}

export const HeaderActions: React.FC<HeaderActionsProps> = ({
  headerActions,
  onActionClick,
  className = "",
}) => {
  // Filter out hidden actions
  const visibleActions = headerActions.filter(action => !action.hidden);

  if (visibleActions.length === 0) {
    return null;
  }

  return (
    <div className={`flex items-center gap-1 ${className}`}>
      {visibleActions.map((action) => (
        <Button
          key={action.name}
          size="sm"
          variant={action.color === 'danger' ? 'destructive' : 'default'}
          onClick={() => onActionClick(action)}
          disabled={action.disabled}
        >
          {action.label}
        </Button>
      ))}
    </div>
  );
};

import * as React from "react";
import { ActionButton } from "./ActionButton";
import type { TableAction } from "../../types";

interface ActionsColumnProps {
  actions: TableAction[];
  record: Record<string, any>;
  onActionClick: (action: TableAction, record: Record<string, any>) => void;
  className?: string;
}

export const ActionsColumn: React.FC<ActionsColumnProps> = ({
  actions,
  record,
  onActionClick,
  className = "",
}) => {
  // Filter out hidden actions
  const visibleActions = actions.filter(action => !action.hidden);

  if (visibleActions.length === 0) {
    return null;
  }

  return (
    <div className={`flex items-center gap-1 ${className}`}>
      {visibleActions.map((action) => (
        <ActionButton
          key={action.name}
          action={action}
          onClick={() => onActionClick(action, record)}
          disabled={action.disabled}
        />
      ))}
    </div>
  );
};
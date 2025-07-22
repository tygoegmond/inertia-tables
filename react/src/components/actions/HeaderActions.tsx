import * as React from "react";
import { Button } from "../ui/button";
import { ActionGroup } from "./ActionGroup";
import type { TableAction, TableActionGroup, TableHeaderActionItem } from "../../types";

interface HeaderActionsProps {
  headerActions: TableHeaderActionItem[];
  onActionClick: (action: TableAction) => void;
  className?: string;
}

export const HeaderActions: React.FC<HeaderActionsProps> = ({
  headerActions,
  onActionClick,
  className = "",
}) => {
  // Filter out hidden actions and groups
  const visibleActions = headerActions.filter(action => {
    if (action.type === 'group') {
      // For groups, check if any child actions are visible
      return action.actions.some(childAction => !childAction.hidden);
    }

    return !action.hidden;
  });

  if (visibleActions.length === 0) {
    return null;
  }

  const handleActionClick = (actionName: string, parentAction?: TableHeaderActionItem) => {
    let targetAction: TableAction;

    if (parentAction && parentAction.type === 'group') {
      // Find the action within the group
      targetAction = parentAction.actions.find((a: TableAction) => a.name === actionName)!;
    } else {
      // Find the action in the main actions list
      targetAction = headerActions.find(a => a.name === actionName) as TableAction;
    }

    if (targetAction) {
      onActionClick(targetAction);
    }
  };

  return (
    <div className={`flex items-center gap-1 ${className}`}>
      {visibleActions.map((actionItem) => {
        if (actionItem.type === 'group') {
          return (
            <ActionGroup
              key={actionItem.name}
              group={actionItem}
              onActionClick={(actionName) => handleActionClick(actionName, actionItem)}
            />
          );
        }

        // Regular header action
        const action = actionItem as TableAction;

        return (
          <Button
            key={action.name}
            size="sm"
            variant={action.color === 'danger' ? 'destructive' : 'default'}
            onClick={() => handleActionClick(action.name)}
            disabled={action.disabled}
            title={action.tooltip}
          >
            {action.label}
          </Button>
        );
      })}
    </div>
  );
};

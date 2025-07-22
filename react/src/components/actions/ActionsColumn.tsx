import * as React from "react";
import { ActionButton } from "./ActionButton";
import { ActionGroup } from "./ActionGroup";
import type { TableAction, TableActionGroup, TableActionItem } from "../../types";

interface ActionsColumnProps {
  actions: TableActionItem[];
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
  // Filter out hidden actions and groups
  const visibleActions = actions.filter(action => {
    if (action.type === 'group') {
      // For groups, check if any child actions are visible
      return action.actions.some(childAction => !childAction.hidden);
    }
    
    return !action.hidden;
  });

  if (visibleActions.length === 0) {
    return null;
  }

  const handleActionClick = (actionName: string, parentAction?: TableActionItem) => {
    let targetAction: TableAction;

    if (parentAction && parentAction.type === 'group') {
      // Find the action within the group
      targetAction = parentAction.actions.find((a: TableAction) => a.name === actionName)!;
    } else {
      // Find the action in the main actions list
      targetAction = actions.find(a => a.name === actionName) as TableAction;
    }

    if (targetAction) {
      onActionClick(targetAction, record);
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

        // Regular action
        const action = actionItem as TableAction;
        
        return (
          <ActionButton
            key={action.name}
            action={action}
            onClick={() => handleActionClick(action.name)}
            disabled={action.disabled}
          />
        );
      })}
    </div>
  );
};
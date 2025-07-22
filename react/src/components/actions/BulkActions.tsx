import * as React from "react";
import { ActionButton } from "./ActionButton";
import { ActionGroup } from "./ActionGroup";
import { Badge } from "../ui/badge";
import type { TableBulkAction, TableBulkActionGroup, TableBulkActionItem } from "../../types";

interface BulkActionsProps {
  bulkActions: TableBulkActionItem[];
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

  // Filter out hidden actions and groups
  const visibleActions = bulkActions.filter(action => {
    if (action.type === 'group') {
      // For groups, check if any child actions are visible
      return action.actions.some(childAction => !childAction.hidden);
    }
    
    return !action.hidden;
  });

  if (visibleActions.length === 0) {
    return null;
  }

  const handleActionClick = (actionName: string, parentAction?: TableBulkActionItem) => {
    let targetAction: TableBulkAction;

    if (parentAction && parentAction.type === 'group') {
      // Find the action within the group
      targetAction = parentAction.actions.find((a: TableBulkAction) => a.name === actionName)!;
    } else {
      // Find the action in the main actions list
      targetAction = bulkActions.find(a => a.name === actionName) as TableBulkAction;
    }

    if (targetAction) {
      onBulkActionClick(targetAction, selectedRecords);
    }
  };

  return (
    <div className={`flex items-center gap-4 p-3 bg-accent/50 border-t ${className}`}>
      <div className="flex items-center gap-2">
        <Badge variant="secondary" className="text-xs">
          {selectedCount} selected
        </Badge>
        
        <div className="h-4 w-px bg-border" />
        
        <div className="flex items-center gap-2">
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

            // Regular bulk action
            const action = actionItem as TableBulkAction;
            
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
      </div>
    </div>
  );
};
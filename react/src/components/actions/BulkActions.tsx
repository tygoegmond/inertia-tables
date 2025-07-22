import * as React from "react";
import { ActionButton } from "./ActionButton";
import { ActionGroup } from "./ActionGroup";
import { Badge } from "../ui/badge";

interface BulkAction {
  name: string;
  label: string;
  icon?: string;
  color: string;
  size: string;
  style: 'button' | 'link' | 'iconButton';
  outlined: boolean;
  tooltip?: string;
  badge?: string;
  badgeColor?: string;
  extraAttributes?: Record<string, any>;
  disabled?: boolean;
  hidden?: boolean;
  hasAction?: boolean;
  requiresConfirmation?: boolean;
  confirmationTitle?: string;
  confirmationMessage?: string;
  confirmationButton?: string;
  cancelButton?: string;
  deselectRecordsAfterCompletion?: boolean;
  type: 'bulk';
}

interface BulkActionGroup {
  type: 'group';
  name: string;
  label: string;
  icon?: string;
  color: string;
  size: string;
  style: 'button' | 'link' | 'iconButton';
  outlined: boolean;
  tooltip?: string;
  extraAttributes?: Record<string, any>;
  actions: BulkAction[];
}

type BulkActionItem = BulkAction | BulkActionGroup;

interface BulkActionsProps {
  bulkActions: BulkActionItem[];
  selectedRecords: Record<string, any>[];
  onBulkActionClick: (action: BulkAction, records: Record<string, any>[]) => void;
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

  const handleActionClick = (actionName: string, parentAction?: BulkActionItem) => {
    let targetAction: BulkAction;

    if (parentAction && parentAction.type === 'group') {
      // Find the action within the group
      targetAction = parentAction.actions.find(a => a.name === actionName)!;
    } else {
      // Find the action in the main actions list
      targetAction = bulkActions.find(a => a.name === actionName) as BulkAction;
    }

    if (targetAction) {
      onBulkActionClick(targetAction, selectedRecords);
    }
  };

  return (
    <div className={`flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-950/50 border-t ${className}`}>
      <div className="flex items-center gap-2">
        <Badge variant="secondary">
          {selectedCount} selected
        </Badge>
        
        <div className="h-4 border-l border-gray-300 dark:border-gray-600" />
        
        <div className="flex items-center gap-1">
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
            const action = actionItem as BulkAction;
            
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
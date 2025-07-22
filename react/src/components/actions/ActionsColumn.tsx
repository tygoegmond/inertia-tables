import * as React from "react";
import { ActionButton } from "./ActionButton";
import { ActionGroup } from "./ActionGroup";

interface Action {
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
  hasUrl?: boolean;
  url?: string;
  openUrlInNewTab?: boolean;
  requiresConfirmation?: boolean;
  confirmationTitle?: string;
  confirmationMessage?: string;
  confirmationButton?: string;
  cancelButton?: string;
}

interface ActionGroup {
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
  actions: Action[];
}

type ActionItem = Action | ActionGroup;

interface ActionsColumnProps {
  actions: ActionItem[];
  record: Record<string, any>;
  onActionClick: (action: Action, record: Record<string, any>) => void;
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

  const handleActionClick = (actionName: string, parentAction?: ActionItem) => {
    let targetAction: Action;

    if (parentAction && parentAction.type === 'group') {
      // Find the action within the group
      targetAction = parentAction.actions.find(a => a.name === actionName)!;
    } else {
      // Find the action in the main actions list
      targetAction = actions.find(a => a.name === actionName) as Action;
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
        const action = actionItem as Action;
        
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
import * as React from "react";
import { ActionButton } from "./ActionButton";
import { ActionGroup } from "./ActionGroup";

interface HeaderAction {
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

interface HeaderActionGroup {
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
  actions: HeaderAction[];
}

type HeaderActionItem = HeaderAction | HeaderActionGroup;

interface HeaderActionsProps {
  headerActions: HeaderActionItem[];
  onActionClick: (action: HeaderAction) => void;
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

  const handleActionClick = (actionName: string, parentAction?: HeaderActionItem) => {
    let targetAction: HeaderAction;

    if (parentAction && parentAction.type === 'group') {
      // Find the action within the group
      targetAction = parentAction.actions.find(a => a.name === actionName)!;
    } else {
      // Find the action in the main actions list
      targetAction = headerActions.find(a => a.name === actionName) as HeaderAction;
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
        const action = actionItem as HeaderAction;
        
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
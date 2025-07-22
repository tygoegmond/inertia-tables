import * as React from "react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "../ui/dropdown-menu";
import { Button } from "../ui/button";
import { cn } from "../../lib/utils";
import * as Icons from "lucide-react";
import { ChevronDown, MoreHorizontal } from "lucide-react";

interface ActionGroupProps {
  group: {
    name: string;
    label: string;
    icon?: string;
    color: string;
    size: string;
    style: 'button' | 'link' | 'iconButton';
    outlined: boolean;
    tooltip?: string;
    extraAttributes?: Record<string, any>;
    actions: Array<{
      name: string;
      label: string;
      icon?: string;
      color: string;
      disabled?: boolean;
      hidden?: boolean;
    }>;
  };
  onActionClick: (actionName: string) => void;
  disabled?: boolean;
  className?: string;
}

export const ActionGroup = React.forwardRef<HTMLButtonElement, ActionGroupProps>(
  ({ group, onActionClick, disabled = false, className, ...props }, ref) => {
    // Filter out hidden actions
    const visibleActions = group.actions.filter(action => !action.hidden);
    
    // Don't render if no visible actions
    if (visibleActions.length === 0) {
      return null;
    }

    // Get the trigger icon component
    const TriggerIconComponent = group.icon 
      ? (Icons as any)[group.icon] || Icons.Circle
      : null;

    // Use MoreHorizontal as default for icon button style
    const DefaultIcon = group.style === 'iconButton' ? MoreHorizontal : ChevronDown;
    const IconToUse = TriggerIconComponent || DefaultIcon;

    // Determine variant based on color and style
    const getVariant = () => {
      if (group.style === 'link') {
        return 'link';
      }
      
      if (group.outlined) {
        return 'outline';
      }
      
      switch (group.color) {
        case 'danger':
          return 'destructive';
        case 'secondary':
        case 'gray':
          return 'secondary';
        case 'ghost':
          return 'ghost';
        default:
          return 'default';
      }
    };

    // Determine size
    const getSize = () => {
      switch (group.size) {
        case 'sm':
          return 'sm';
        case 'lg':
          return 'lg';
        default:
          return 'default';
      }
    };

    const triggerContent = () => {
      if (group.style === 'iconButton') {
        return <IconToUse className="h-4 w-4" />;
      }

      return (
        <>
          {TriggerIconComponent && <TriggerIconComponent className="h-4 w-4 mr-2" />}
          {group.label}
          <ChevronDown className="h-4 w-4 ml-2" />
        </>
      );
    };

    return (
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button
            ref={ref}
            variant={getVariant()}
            size={group.style === 'iconButton' ? 'icon' : getSize()}
            disabled={disabled}
            className={cn(className)}
            title={group.tooltip}
            {...group.extraAttributes}
            {...props}
          >
            {triggerContent()}
          </Button>
        </DropdownMenuTrigger>
        
        <DropdownMenuContent align="end" className="w-48">
          {visibleActions.map((action) => {
            const ActionIconComponent = action.icon 
              ? (Icons as any)[action.icon] || Icons.Circle
              : null;

            return (
              <DropdownMenuItem
                key={action.name}
                onClick={() => onActionClick(action.name)}
                disabled={action.disabled}
                className={cn(
                  "flex items-center cursor-pointer",
                  action.color === 'danger' && "text-red-600 focus:text-red-600"
                )}
              >
                {ActionIconComponent && (
                  <ActionIconComponent className="h-4 w-4 mr-2" />
                )}
                {action.label}
              </DropdownMenuItem>
            );
          })}
        </DropdownMenuContent>
      </DropdownMenu>
    );
  }
);

ActionGroup.displayName = "ActionGroup";
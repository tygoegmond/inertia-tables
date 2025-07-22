import * as React from "react";
import { Button } from "../ui/button";
import { Badge } from "../ui/badge";
import { cn } from "../../lib/utils";

interface ActionButtonProps {
  action: {
    name: string;
    label: string;
    color: string;
    style: 'button' | 'link' | 'iconButton';
    outlined: boolean;
    tooltip?: string;
    badge?: string;
    badgeColor?: string;
    extraAttributes?: Record<string, any>;
    disabled?: boolean;
  };
  onClick: () => void;
  disabled?: boolean;
  className?: string;
}

export const ActionButton = React.forwardRef<HTMLButtonElement, ActionButtonProps>(
  ({ action, onClick, disabled = false, className, ...props }, ref) => {
    const isDisabled = disabled || action.disabled;

    // Determine variant based on color and style
    const getVariant = () => {
      if (action.style === 'link') {
        return 'link';
      }

      if (action.outlined) {
        return 'outline';
      }

      switch (action.color) {
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

    return (
      <div className="relative inline-block">
        <Button
          ref={ref}
          variant={getVariant()}
          size="sm"
          disabled={isDisabled}
          onClick={onClick}
          className={cn(className)}
          title={action.tooltip}
          {...action.extraAttributes}
          {...props}
        >
          {action.label}
        </Button>

        {/* Badge */}
        {action.badge && (
          <Badge
            variant={
              action.badgeColor === 'danger' ? "destructive" :
              action.badgeColor === 'success' ? "default" :
              action.badgeColor === 'warning' ? "secondary" :
              action.badgeColor === 'info' ? "default" :
              "secondary"
            }
            className="absolute -top-1 -right-1 h-5 w-5 rounded-full p-0 text-xs flex items-center justify-center"
          >
            {action.badge}
          </Badge>
        )}
      </div>
    );
  }
);

ActionButton.displayName = "ActionButton";

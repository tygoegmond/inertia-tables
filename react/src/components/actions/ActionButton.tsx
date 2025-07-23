import * as React from "react";
import { Button } from "../ui/button";
import { cn } from "../../lib/utils";

interface ActionButtonProps {
  action: {
    name: string;
    label: string;
    color: string;
    disabled?: boolean;
  };
  onClick: () => void;
  disabled?: boolean;
  className?: string;
}

export const ActionButton = React.forwardRef<HTMLButtonElement, ActionButtonProps>(
  ({ action, onClick, disabled = false, className, ...props }, ref) => {
    const isDisabled = disabled || action.disabled;

    // Determine variant based on color
    const getVariant = () => {
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
      <Button
        ref={ref}
        variant={getVariant()}
        size="sm"
        disabled={isDisabled}
        onClick={onClick}
        className={cn(className)}
        {...props}
      >
        {action.label}
      </Button>
    );
  }
);

ActionButton.displayName = "ActionButton";

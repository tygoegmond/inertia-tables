import * as React from "react";
import { Button } from "../ui/button";
import { cn } from "../../lib/utils";
import * as Icons from "lucide-react";

interface ActionButtonProps {
  action: {
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
  };
  onClick: () => void;
  disabled?: boolean;
  className?: string;
}

export const ActionButton = React.forwardRef<HTMLButtonElement, ActionButtonProps>(
  ({ action, onClick, disabled = false, className, ...props }, ref) => {
    const isDisabled = disabled || action.disabled;
    
    // Get the icon component
    const IconComponent = action.icon 
      ? (Icons as any)[action.icon] || Icons.Circle
      : null;

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

    // Determine size
    const getSize = () => {
      switch (action.size) {
        case 'sm':
          return 'sm';
        case 'lg':
          return 'lg';
        case 'icon':
          return 'icon';
        default:
          return 'default';
      }
    };

    const buttonContent = () => {
      if (action.style === 'iconButton') {
        return IconComponent ? <IconComponent className="h-4 w-4" /> : null;
      }

      return (
        <>
          {IconComponent && <IconComponent className="h-4 w-4 mr-2" />}
          {action.label}
        </>
      );
    };

    return (
      <div className="relative inline-block">
        <Button
          ref={ref}
          variant={getVariant()}
          size={action.style === 'iconButton' ? 'icon' : getSize()}
          disabled={isDisabled}
          onClick={onClick}
          className={cn(className)}
          title={action.tooltip}
          {...action.extraAttributes}
          {...props}
        >
          {buttonContent()}
        </Button>
        
        {/* Badge */}
        {action.badge && (
          <span className={cn(
            "absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-medium rounded-full",
            action.badgeColor === 'danger' ? "bg-red-500 text-white" :
            action.badgeColor === 'success' ? "bg-green-500 text-white" :
            action.badgeColor === 'warning' ? "bg-yellow-500 text-white" :
            action.badgeColor === 'info' ? "bg-blue-500 text-white" :
            "bg-gray-500 text-white"
          )}>
            {action.badge}
          </span>
        )}
      </div>
    );
  }
);

ActionButton.displayName = "ActionButton";
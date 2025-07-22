import * as React from "react";
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
export declare const ActionButton: React.ForwardRefExoticComponent<ActionButtonProps & React.RefAttributes<HTMLButtonElement>>;
export {};

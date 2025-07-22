import * as React from "react";
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
export declare const ActionGroup: React.ForwardRefExoticComponent<ActionGroupProps & React.RefAttributes<HTMLButtonElement>>;
export {};

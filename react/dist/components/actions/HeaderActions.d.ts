import * as React from "react";
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
export declare const HeaderActions: React.FC<HeaderActionsProps>;
export {};

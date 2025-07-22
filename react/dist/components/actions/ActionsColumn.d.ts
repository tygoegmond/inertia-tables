import * as React from "react";
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
export declare const ActionsColumn: React.FC<ActionsColumnProps>;
export {};

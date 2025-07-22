import * as React from "react";
interface BulkAction {
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
    requiresConfirmation?: boolean;
    confirmationTitle?: string;
    confirmationMessage?: string;
    confirmationButton?: string;
    cancelButton?: string;
    deselectRecordsAfterCompletion?: boolean;
    type: 'bulk';
}
interface BulkActionGroup {
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
    actions: BulkAction[];
}
type BulkActionItem = BulkAction | BulkActionGroup;
interface BulkActionsProps {
    bulkActions: BulkActionItem[];
    selectedRecords: Record<string, any>[];
    onBulkActionClick: (action: BulkAction, records: Record<string, any>[]) => void;
    className?: string;
}
export declare const BulkActions: React.FC<BulkActionsProps>;
export {};

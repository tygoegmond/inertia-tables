import * as React from "react";
interface ActionConfirmationDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    message: string;
    confirmButton: string;
    cancelButton: string;
    onConfirm: () => void;
    onCancel?: () => void;
    isLoading?: boolean;
}
export declare const ActionConfirmationDialog: React.FC<ActionConfirmationDialogProps>;
export {};

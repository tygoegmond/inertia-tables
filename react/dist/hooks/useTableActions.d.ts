interface Action {
    name: string;
    label: string;
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
interface BulkAction {
    name: string;
    label: string;
    hasAction?: boolean;
    requiresConfirmation?: boolean;
    confirmationTitle?: string;
    confirmationMessage?: string;
    confirmationButton?: string;
    cancelButton?: string;
    deselectRecordsAfterCompletion?: boolean;
    type: 'bulk';
}
interface UseTableActionsProps {
    tableName: string;
    onSuccess?: (message?: string) => void;
    onError?: (error: string) => void;
}
interface UseTableActionsReturn {
    isLoading: boolean;
    confirmationDialog: {
        isOpen: boolean;
        title: string;
        message: string;
        confirmButton: string;
        cancelButton: string;
    };
    formDialog: {
        isOpen: boolean;
        title: string;
        description?: string;
        fields: any[];
        submitButton: string;
        cancelButton: string;
    };
    executeAction: (action: Action, record?: Record<string, any>) => void;
    executeBulkAction: (action: BulkAction, records: Record<string, any>[]) => void;
    executeHeaderAction: (action: Action) => void;
    confirmAction: () => void;
    cancelAction: () => void;
    submitForm: (data: Record<string, any>) => void;
}
export declare const useTableActions: ({ tableName, onSuccess, onError, }: UseTableActionsProps) => UseTableActionsReturn;
export {};

interface DialogState {
    isOpen: boolean;
    title: string;
    message: string;
    confirmButton: string;
    cancelButton: string;
}
interface FormDialogState {
    isOpen: boolean;
    title: string;
    description?: string;
    fields: any[];
    submitButton: string;
    cancelButton: string;
    initialData?: Record<string, any>;
}
interface UseActionDialogReturn {
    confirmationDialog: DialogState;
    formDialog: FormDialogState;
    showConfirmation: (config: Partial<DialogState>) => void;
    showForm: (config: Partial<FormDialogState>) => void;
    hideConfirmation: () => void;
    hideForm: () => void;
    hideAll: () => void;
}
export declare const useActionDialog: () => UseActionDialogReturn;
export {};

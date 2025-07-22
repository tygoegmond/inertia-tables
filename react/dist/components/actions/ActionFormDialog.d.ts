import * as React from "react";
import { FormField } from "../ui/form";
interface FormField {
    name: string;
    label: string;
    type: 'text' | 'email' | 'password' | 'textarea' | 'select';
    required?: boolean;
    placeholder?: string;
    options?: Array<{
        value: string;
        label: string;
    }>;
}
interface ActionFormDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description?: string;
    fields: FormField[];
    onSubmit: (data: Record<string, any>) => void;
    onCancel?: () => void;
    isLoading?: boolean;
    submitButton?: string;
    cancelButton?: string;
    initialData?: Record<string, any>;
}
export declare const ActionFormDialog: React.FC<ActionFormDialogProps>;
export {};

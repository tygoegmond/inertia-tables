import { useState, useCallback } from 'react';

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

export const useActionDialog = (): UseActionDialogReturn => {
  const [confirmationDialog, setConfirmationDialog] = useState<DialogState>({
    isOpen: false,
    title: '',
    message: '',
    confirmButton: 'Confirm',
    cancelButton: 'Cancel',
  });

  const [formDialog, setFormDialog] = useState<FormDialogState>({
    isOpen: false,
    title: '',
    description: '',
    fields: [],
    submitButton: 'Submit',
    cancelButton: 'Cancel',
    initialData: {},
  });

  const showConfirmation = useCallback((config: Partial<DialogState>) => {
    setConfirmationDialog((prev) => ({
      ...prev,
      ...config,
      isOpen: true,
    }));
  }, []);

  const showForm = useCallback((config: Partial<FormDialogState>) => {
    setFormDialog((prev) => ({
      ...prev,
      ...config,
      isOpen: true,
    }));
  }, []);

  const hideConfirmation = useCallback(() => {
    setConfirmationDialog((prev) => ({ ...prev, isOpen: false }));
  }, []);

  const hideForm = useCallback(() => {
    setFormDialog((prev) => ({ ...prev, isOpen: false }));
  }, []);

  const hideAll = useCallback(() => {
    setConfirmationDialog((prev) => ({ ...prev, isOpen: false }));
    setFormDialog((prev) => ({ ...prev, isOpen: false }));
  }, []);

  return {
    confirmationDialog,
    formDialog,
    showConfirmation,
    showForm,
    hideConfirmation,
    hideForm,
    hideAll,
  };
};

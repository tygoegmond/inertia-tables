import { useState, useCallback } from "react";
import { router } from "@inertiajs/react";

interface Action {
  name: string;
  label: string;
  hasAction?: boolean;
  hasUrl?: boolean;
  url?: string;
  actionUrl?: string;
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
  actionUrl?: string;
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

export const useTableActions = ({
  tableName,
  onSuccess,
  onError,
}: UseTableActionsProps): UseTableActionsReturn => {
  const [isLoading, setIsLoading] = useState(false);
  
  const [confirmationDialog, setConfirmationDialog] = useState({
    isOpen: false,
    title: "",
    message: "",
    confirmButton: "Confirm",
    cancelButton: "Cancel",
  });

  const [formDialog, setFormDialog] = useState({
    isOpen: false,
    title: "",
    description: "",
    fields: [],
    submitButton: "Submit",
    cancelButton: "Cancel",
  });

  const [pendingAction, setPendingAction] = useState<{
    action: Action | BulkAction;
    record?: Record<string, any>;
    records?: Record<string, any>[];
    type: 'single' | 'bulk' | 'header';
  } | null>(null);

  const handleUrlAction = useCallback((action: Action, record?: Record<string, any>) => {
    if (!action.url) return;

    let url = action.url;
    
    // Replace record placeholders in URL if record is provided
    if (record) {
      Object.keys(record).forEach(key => {
        url = url.replace(`{${key}}`, record[key]);
      });
    }

    if (action.openUrlInNewTab) {
      window.open(url, '_blank');
    } else {
      router.visit(url);
    }
  }, []);

  const performActionRequest = useCallback(async (
    action: Action | BulkAction,
    records: Record<string, any>[] = []
  ) => {
    setIsLoading(true);
    
    try {
      const recordIds = records.map(record => record.id || record.key);
      
      if (!action.actionUrl) {
        throw new Error(`Action ${action.name} does not have a valid actionUrl`);
      }
      
      router.post(action.actionUrl, {
        records: recordIds,
      }, {
        onSuccess: (page) => {
          const result = page.props as any;
          if (result.redirect_url) {
            router.visit(result.redirect_url);
          } else {
            onSuccess?.(result.message);
          }
        },
        onError: (errors) => {
          const errorMessage = typeof errors === 'string' ? errors : 
            Object.values(errors)[0] as string || 'Action failed';
          onError?.(errorMessage);
        },
        onFinish: () => {
          setIsLoading(false);
          setPendingAction(null);
          setConfirmationDialog(prev => ({ ...prev, isOpen: false }));
        }
      });
    } catch (error) {
      onError?.(error instanceof Error ? error.message : 'Action failed');
      setIsLoading(false);
      setPendingAction(null);
      setConfirmationDialog(prev => ({ ...prev, isOpen: false }));
    }
  }, [onSuccess, onError]);

  const executeAction = useCallback((action: Action, record?: Record<string, any>) => {
    if (action.hasUrl && action.url) {
      handleUrlAction(action, record);
      return;
    }

    if (!action.hasAction) {
      console.warn('Action has no URL or action handler');
      return;
    }

    if (action.requiresConfirmation) {
      setPendingAction({ action, record, type: 'single' });
      setConfirmationDialog({
        isOpen: true,
        title: action.confirmationTitle || 'Confirm Action',
        message: action.confirmationMessage || 'Are you sure you want to perform this action?',
        confirmButton: action.confirmationButton || 'Confirm',
        cancelButton: action.cancelButton || 'Cancel',
      });
    } else {
      performActionRequest(action, record ? [record] : []);
    }
  }, [handleUrlAction, performActionRequest]);

  const executeBulkAction = useCallback((action: BulkAction, records: Record<string, any>[]) => {
    if (!action.hasAction) {
      console.warn('Bulk action has no action handler');
      return;
    }

    if (action.requiresConfirmation) {
      setPendingAction({ action, records, type: 'bulk' });
      setConfirmationDialog({
        isOpen: true,
        title: action.confirmationTitle || 'Confirm Bulk Action',
        message: action.confirmationMessage || `Are you sure you want to perform this action on ${records.length} records?`,
        confirmButton: action.confirmationButton || 'Confirm',
        cancelButton: action.cancelButton || 'Cancel',
      });
    } else {
      performActionRequest(action, records);
    }
  }, [performActionRequest]);

  const executeHeaderAction = useCallback((action: Action) => {
    if (action.hasUrl && action.url) {
      handleUrlAction(action);
      return;
    }

    if (!action.hasAction) {
      console.warn('Header action has no URL or action handler');
      return;
    }

    if (action.requiresConfirmation) {
      setPendingAction({ action, type: 'header' });
      setConfirmationDialog({
        isOpen: true,
        title: action.confirmationTitle || 'Confirm Action',
        message: action.confirmationMessage || 'Are you sure you want to perform this action?',
        confirmButton: action.confirmationButton || 'Confirm',
        cancelButton: action.cancelButton || 'Cancel',
      });
    } else {
      performActionRequest(action, []);
    }
  }, [handleUrlAction, performActionRequest]);

  const confirmAction = useCallback(() => {
    if (!pendingAction) return;

    const { action, record, records } = pendingAction;
    
    if (pendingAction.type === 'bulk' && records) {
      performActionRequest(action, records);
    } else if (pendingAction.type === 'single' && record) {
      performActionRequest(action, [record]);
    } else {
      performActionRequest(action, []);
    }
  }, [pendingAction, performActionRequest]);

  const cancelAction = useCallback(() => {
    setPendingAction(null);
    setConfirmationDialog(prev => ({ ...prev, isOpen: false }));
    setFormDialog(prev => ({ ...prev, isOpen: false }));
  }, []);

  const submitForm = useCallback((data: Record<string, any>) => {
    // Handle form submission
    // This would extend the action request to include form data
    console.log('Form submitted:', data);
  }, []);

  return {
    isLoading,
    confirmationDialog,
    formDialog,
    executeAction,
    executeBulkAction,
    executeHeaderAction,
    confirmAction,
    cancelAction,
    submitForm,
  };
};
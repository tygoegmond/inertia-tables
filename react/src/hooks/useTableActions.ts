import { useState, useCallback } from 'react';
import { router } from '@inertiajs/react';
import { TableAction, TableBulkAction } from '../types';
import type { MergedAction } from '../lib/actions';

interface UseTableActionsProps {
  tableName: string;
  primaryKey: string;
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
    variant:
      | 'default'
      | 'destructive'
      | 'outline'
      | 'secondary'
      | 'ghost'
      | 'link';
  };
  formDialog: {
    isOpen: boolean;
    title: string;
    description?: string;
    fields: any[];
    submitButton: string;
    cancelButton: string;
  };
  executeAction: (action: MergedAction, record?: Record<string, any>) => void;
  executeBulkAction: (
    action: TableBulkAction,
    records: Record<string, any>[]
  ) => void;
  executeHeaderAction: (action: TableAction) => void;
  confirmAction: () => void;
  cancelAction: () => void;
  submitForm: (data: Record<string, any>) => void;
  handleCallback: (callback: string, openInNewTab?: boolean) => void;
}

export const useTableActions = ({
  tableName: _tableName,
  primaryKey,
  onSuccess,
  onError,
}: UseTableActionsProps): UseTableActionsReturn => {
  const [isLoading, setIsLoading] = useState(false);

  const [confirmationDialog, setConfirmationDialog] = useState({
    isOpen: false,
    title: '',
    message: '',
    confirmButton: 'Confirm',
    cancelButton: 'Cancel',
    variant: 'destructive' as const,
  });

  const [formDialog, setFormDialog] = useState({
    isOpen: false,
    title: '',
    description: '',
    fields: [],
    submitButton: 'Submit',
    cancelButton: 'Cancel',
  });

  const [pendingAction, setPendingAction] = useState<{
    action: MergedAction | TableBulkAction;
    record?: Record<string, any>;
    records?: Record<string, any>[];
  } | null>(null);

  const handleCallback = useCallback(
    (callback: string, openInNewTab?: boolean) => {
      if (openInNewTab) {
        window.open(callback, '_blank');
      } else {
        router.visit(callback);
      }
    },
    []
  );

  const performActionRequest = useCallback(
    async (
      action: MergedAction | TableBulkAction,
      records: Record<string, any>[] = [],
      isBulkAction: boolean = false
    ) => {
      setIsLoading(true);

      try {
        if (!action.callback) {
          throw new Error(
            `Action ${action.name} does not have a valid callback`
          );
        }

        // For regular actions: send empty POST body (record comes from signed URL)
        // For bulk actions: send records in POST body (requires authorization)
        const postData = isBulkAction
          ? { records: records.map((record) => record[primaryKey]) }
          : {};

        router.post(action.callback, postData, {
          onSuccess: (page) => {
            const result = page.props as any;
            if (result.redirect_url) {
              router.visit(result.redirect_url);
            } else {
              onSuccess?.(result.message);
            }
          },
          onError: (errors) => {
            const errorMessage =
              typeof errors === 'string'
                ? errors
                : (Object.values(errors)[0] as string) || 'Action failed';
            onError?.(errorMessage);
          },
          onFinish: () => {
            setIsLoading(false);
            setPendingAction(null);
            setConfirmationDialog((prev) => ({ ...prev, isOpen: false }));
          },
        });
      } catch (error) {
        onError?.(error instanceof Error ? error.message : 'Action failed');
        setIsLoading(false);
        setPendingAction(null);
        setConfirmationDialog((prev) => ({ ...prev, isOpen: false }));
      }
    },
    [onSuccess, onError]
  );

  const executeAction = useCallback(
    (action: MergedAction, record?: Record<string, any>) => {
      if (!action.callback) {
        console.warn('Action has no callback');
        return;
      }

      if (action.requiresConfirmation) {
        setPendingAction({ action, record });
        setConfirmationDialog({
          isOpen: true,
          title: action.confirmationTitle || 'Confirm Action',
          message:
            action.confirmationMessage ||
            'Are you sure you want to perform this action?',
          confirmButton: action.confirmationButton || 'Confirm',
          cancelButton: action.cancelButton || 'Cancel',
          variant: 'destructive',
        });
      } else {
        performActionRequest(action, record ? [record] : [], false);
      }
    },
    [performActionRequest]
  );

  const executeBulkAction = useCallback(
    (action: TableBulkAction, records: Record<string, any>[]) => {
      if (!action.callback) {
        console.warn('Bulk action has no callback');
        return;
      }

      if (action.requiresConfirmation) {
        setPendingAction({ action, records });
        setConfirmationDialog({
          isOpen: true,
          title: action.confirmationTitle || 'Confirm Bulk Action',
          message:
            action.confirmationMessage ||
            `Are you sure you want to perform this action on ${records.length} records?`,
          confirmButton: action.confirmationButton || 'Confirm',
          cancelButton: action.cancelButton || 'Cancel',
          variant: 'destructive',
        });
      } else {
        performActionRequest(action, records, true);
      }
    },
    [performActionRequest]
  );

  const executeHeaderAction = useCallback(
    (action: TableAction) => {
      if (!action.hasAction) {
        console.warn('Header action has no action handler');
        return;
      }

      if (action.requiresConfirmation) {
        setPendingAction({ action });
        setConfirmationDialog({
          isOpen: true,
          title: action.confirmationTitle || 'Confirm Action',
          message:
            action.confirmationMessage ||
            'Are you sure you want to perform this action?',
          confirmButton: action.confirmationButton || 'Confirm',
          cancelButton: action.cancelButton || 'Cancel',
          variant: 'destructive',
        });
      } else {
        performActionRequest(action, [], false);
      }
    },
    [performActionRequest]
  );

  const confirmAction = useCallback(() => {
    if (!pendingAction) return;

    const { action, record, records } = pendingAction;

    if (records) {
      // This is a bulk action
      performActionRequest(action, records, true);
    } else if (record) {
      // This is a regular action with single record
      performActionRequest(action, [record], false);
    } else {
      // This is a header action
      performActionRequest(action, [], false);
    }
  }, [pendingAction, performActionRequest]);

  const cancelAction = useCallback(() => {
    setPendingAction(null);
    setConfirmationDialog((prev) => ({ ...prev, isOpen: false }));
    setFormDialog((prev) => ({ ...prev, isOpen: false }));
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
    handleCallback,
  };
};

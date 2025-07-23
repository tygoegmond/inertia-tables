import * as React from 'react';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '../ui/alert-dialog';
import { buttonVariants } from '../ui/button';
import { cn } from '../../lib/utils';

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
  variant?:
    | 'default'
    | 'destructive'
    | 'outline'
    | 'secondary'
    | 'ghost'
    | 'link';
}

export const ActionConfirmationDialog: React.FC<
  ActionConfirmationDialogProps
> = ({
  open,
  onOpenChange,
  title,
  message,
  confirmButton,
  cancelButton,
  onConfirm,
  onCancel,
  isLoading = false,
  variant = 'destructive',
}) => {
  const handleConfirm = () => {
    onConfirm();
  };

  const handleCancel = () => {
    onCancel?.();
    onOpenChange(false);
  };

  const handleOpenChange = (isOpen: boolean) => {
    if (!isLoading) {
      onOpenChange(isOpen);
      if (!isOpen) {
        onCancel?.();
      }
    }
  };

  return (
    <AlertDialog open={open} onOpenChange={handleOpenChange}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>{title}</AlertDialogTitle>
          <AlertDialogDescription>{message}</AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel onClick={handleCancel} disabled={isLoading}>
            {cancelButton}
          </AlertDialogCancel>
          <AlertDialogAction
            onClick={handleConfirm}
            disabled={isLoading}
            className={cn(buttonVariants({ variant }))}
          >
            {isLoading ? 'Processing...' : confirmButton}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
};

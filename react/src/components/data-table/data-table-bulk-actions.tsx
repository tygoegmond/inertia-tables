import { DropdownMenuTrigger } from '@radix-ui/react-dropdown-menu';
import { Wrench } from 'lucide-react';

import { Button } from '../ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
} from '../ui/dropdown-menu';
import { Badge } from '../ui/badge';
import type { TableBulkAction } from '../../types';

interface DataTableBulkActionsProps {
  bulkActions: TableBulkAction[];
  selectedRecords: Record<string, unknown>[];
  onBulkActionClick: (
    action: TableBulkAction,
    records: Record<string, unknown>[]
  ) => void;
}

export function DataTableBulkActions({
  bulkActions,
  selectedRecords,
  onBulkActionClick,
}: DataTableBulkActionsProps) {
  const selectedCount = selectedRecords.length;

  // Don't render if no records are selected
  if (selectedCount === 0) {
    return null;
  }

  if (bulkActions.length === 0) {
    return null;
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          variant="outline"
          size="sm"
          className="ml-auto hidden h-8 lg:flex"
        >
          <Wrench />
          Actions
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-[160px]">
        <DropdownMenuLabel className="flex items-center gap-2">
          Actions
          <Badge variant="secondary" className="text-xs">
            {selectedCount}
          </Badge>
        </DropdownMenuLabel>
        <DropdownMenuSeparator />
        {bulkActions.map((action, index) => (
          <div key={action.name}>
            <DropdownMenuItem
              onClick={() => onBulkActionClick(action, selectedRecords)}
              className={
                action.color === 'danger'
                  ? 'text-destructive focus:text-destructive'
                  : ''
              }
            >
              {action.label}
            </DropdownMenuItem>
            {action.color === 'danger' && index < bulkActions.length - 1 && (
              <DropdownMenuSeparator />
            )}
          </div>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}

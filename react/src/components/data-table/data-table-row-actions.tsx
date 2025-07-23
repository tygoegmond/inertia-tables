import { Row } from '@tanstack/react-table';
import { MoreHorizontal } from 'lucide-react';

import { Button } from '../ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuShortcut,
  DropdownMenuTrigger,
} from '../ui/dropdown-menu';
import { TableAction, TableRowData } from '../../types';
import { getRowActions, MergedAction } from '../../lib/actions';

interface DataTableRowActionsProps<TData extends TableRowData> {
  row: Row<TData>;
  staticActions: TableAction[];
  onActionClick: (action: MergedAction, record: TData) => void;
}

export function DataTableRowActions<TData extends TableRowData>({
  row,
  staticActions,
  onActionClick,
}: DataTableRowActionsProps<TData>) {
  const actions = getRowActions(staticActions, row.original);

  if (actions.length === 0) {
    return null;
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          variant="ghost"
          size="icon"
          className="data-[state=open]:bg-muted size-8"
        >
          <MoreHorizontal />
          <span className="sr-only">Open menu</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-[160px]">
        {actions.map((action, index) => (
          <div key={action.name}>
            <DropdownMenuItem
              onClick={() => onActionClick(action, row.original)}
              disabled={action.disabled}
              className={
                action.color === 'danger'
                  ? 'text-destructive focus:text-destructive'
                  : ''
              }
            >
              {action.label}
            </DropdownMenuItem>
            {action.color === 'danger' && index < actions.length - 1 && (
              <DropdownMenuSeparator />
            )}
          </div>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
}

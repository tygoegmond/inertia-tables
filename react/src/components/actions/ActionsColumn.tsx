import * as React from 'react';
import { ActionButton } from './ActionButton';
import type { TableAction, TableRowData } from '../../types';
import { getRowActions, MergedAction } from '../../lib/actions';

interface ActionsColumnProps {
  staticActions: TableAction[];
  record: TableRowData;
  onActionClick: (action: MergedAction, record: TableRowData) => void;
  className?: string;
}

export const ActionsColumn: React.FC<ActionsColumnProps> = ({
  staticActions,
  record,
  onActionClick,
  className = '',
}) => {
  const actions = getRowActions(staticActions, record);

  if (actions.length === 0) {
    return null;
  }

  return (
    <div className={`flex items-center gap-1 ${className}`}>
      {actions.map((action) => (
        <ActionButton
          key={action.name}
          action={action}
          onClick={() => onActionClick(action, record)}
          disabled={action.disabled}
        />
      ))}
    </div>
  );
};

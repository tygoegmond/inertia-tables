import * as React from 'react';
import { ChevronUp, ChevronDown } from 'lucide-react';
import { TableColumn } from '../../types';

interface SortableHeaderProps {
  column: TableColumn;
  sortDirection?: 'asc' | 'desc' | null;
  onSort?: (column: string, direction: 'asc' | 'desc') => void;
  className?: string;
}

export const SortableHeader = React.memo<SortableHeaderProps>(
  ({ column, sortDirection, onSort, className = '' }) => {
    const isActive = sortDirection !== null && sortDirection !== undefined;

    const handleClick = React.useCallback(() => {
      if (column.sortable && onSort) {
        const newDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        onSort(column.key, newDirection);
      }
    }, [column.key, column.sortable, onSort, sortDirection]);

    const handleKeyDown = React.useCallback(
      (event: React.KeyboardEvent) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          handleClick();
        }
      },
      [handleClick]
    );

    if (!column.sortable) {
      return <span className={className}>{column.label}</span>;
    }

    return (
      <div
        className={`flex items-center gap-2 cursor-pointer select-none hover:text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 rounded-sm ${className}`}
        onClick={handleClick}
        onKeyDown={handleKeyDown}
        tabIndex={0}
        role="button"
        aria-label={`Sort by ${column.label} ${
          sortDirection === 'asc'
            ? 'descending'
            : sortDirection === 'desc'
              ? 'ascending'
              : 'ascending'
        }`}
        aria-pressed={isActive}
      >
        {column.label}
        <div className="flex flex-col" aria-hidden="true">
          <ChevronUp
            className={`h-3 w-3 ${
              isActive && sortDirection === 'asc'
                ? 'text-foreground'
                : 'text-muted-foreground'
            }`}
          />
          <ChevronDown
            className={`h-3 w-3 -mt-1 ${
              isActive && sortDirection === 'desc'
                ? 'text-foreground'
                : 'text-muted-foreground'
            }`}
          />
        </div>
      </div>
    );
  }
);

SortableHeader.displayName = 'SortableHeader';

import React from 'react';

export interface TableColumn {
  key: string;
  label: string;
  type: 'text';
  visible: boolean;
  sortable: boolean;
  searchable: boolean;
  searchColumn: string | null;
  defaultSort: 'asc' | 'desc' | null;
  state: Record<string, unknown>;
  prefix?: string;
  suffix?: string;
  copyable?: boolean;
  limit?: number;
  wrap?: 'truncate' | 'break-words';
  badge?: boolean;
  icon?: string;
  size?: number;
  rounded?: boolean;
}

export interface TableConfig {
  columns: TableColumn[];
  searchable: boolean;
  selectable?: boolean;
  perPage: number;
  defaultSort: Record<string, 'asc' | 'desc'>;
}

export interface TablePagination {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number | null;
  to: number | null;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

export interface TableAction {
  name: string;
  label: string;
  color: string;
  hasAction?: boolean;
  hasUrl?: boolean;
  requiresConfirmation?: boolean;
  confirmationTitle?: string;
  confirmationMessage?: string;
  confirmationButton?: string;
  cancelButton?: string;
  type?: 'action';
}

export interface RowActionData {
  callback?: string;
  disabled?: boolean;
  openUrlInNewTab?: boolean;
}

export interface TableBulkAction {
  name: string;
  label: string;
  color: string;
  hasAction?: boolean;
  callback?: string;
  requiresConfirmation?: boolean;
  confirmationTitle?: string;
  confirmationMessage?: string;
  confirmationButton?: string;
  cancelButton?: string;
}

export type TableActionItem = TableAction;
export type TableBulkActionItem = TableBulkAction;
export type TableHeaderActionItem = TableAction;

export interface TableRowData {
  [key: string]: unknown;
  actions?: Record<string, RowActionData>;
}

export interface TableResult<T = Record<string, unknown>> {
  config: TableConfig;
  data: (T & TableRowData)[];
  pagination: TablePagination;
  sort: Record<string, 'asc' | 'desc'>;
  search: string | null;
  name?: string | null;
  actions?: TableActionItem[];
  bulkActions?: TableBulkActionItem[];
  headerActions?: TableHeaderActionItem[];
  primaryKey?: string | null;
}

export interface TableProps<T = Record<string, unknown>> {
  state: TableResult<T> | undefined;
  className?: string;
}

// Hook interfaces
export interface UseTableStateProps {
  result: TableResult | undefined;
  onSort?: (column: string | null, direction: 'asc' | 'desc' | null) => void;
}

export interface UseTableStateResult {
  sorting: import('@tanstack/react-table').SortingState;
  setSorting: React.Dispatch<
    React.SetStateAction<import('@tanstack/react-table').SortingState>
  >;
  handleSort: (column: string | null, direction: 'asc' | 'desc' | null) => void;
  isLoading: boolean;
  error: Error | null;
}

export interface UseTableColumnsProps {
  result: TableResult | undefined;
  renderCell?: (
    column: TableColumn,
    value: unknown,
    record: Record<string, unknown>
  ) => React.ReactNode;
}

export interface UseInertiaTableResult {
  searchValue: string;
  setSearchValue: React.Dispatch<React.SetStateAction<string>>;
  handleSearch: (query: string) => void;
  handleSort: (column: string | null, direction: 'asc' | 'desc' | null) => void;
  handlePageChange: (page: number) => void;
  isNavigating: boolean;
}

export interface TableColumn {
  key: string;
  label: string;
  type: 'text';
  visible: boolean;
  sortable: boolean;
  searchable: boolean;
  searchColumn: string | null;
  defaultSort: 'asc' | 'desc' | null;
  state: any;
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

export interface TableResult<T = any> {
  config: TableConfig;
  data: T[];
  pagination: TablePagination;
  sort: Record<string, 'asc' | 'desc'>;
  search: string | null;
  name?: string | null;
}

export interface TableProps<T = any> {
  state: TableResult<T> | undefined;
  className?: string;
}

// Legacy interface for backward compatibility
export interface RequiredTableProps<T = any> {
  state: TableResult<T>;
  className?: string;
}

// Hook interfaces  
export interface UseTableStateProps {
  result: TableResult | undefined;
  onSort?: (column: string, direction: 'asc' | 'desc') => void;
}

export interface UseTableStateResult {
  sorting: import('@tanstack/react-table').SortingState;
  setSorting: React.Dispatch<React.SetStateAction<import('@tanstack/react-table').SortingState>>;
  handleSort: (column: string, direction: 'asc' | 'desc') => void;
  isLoading: boolean;
  error: Error | null;
}

export interface UseTableColumnsProps {
  result: TableResult | undefined;
  renderCell?: (column: TableColumn, value: any, record: any) => React.ReactNode;
}

export interface UseInertiaTableResult {
  searchValue: string;
  setSearchValue: React.Dispatch<React.SetStateAction<string>>;
  handleSearch: (query: string) => void;
  handleSort: (column: string, direction: 'asc' | 'desc') => void;
  handlePageChange: (page: number) => void;
  isNavigating: boolean;
}
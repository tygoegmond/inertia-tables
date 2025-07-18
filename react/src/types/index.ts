export interface TableColumn {
  key: string;
  label: string;
  type: 'text' | 'badge' | 'icon' | 'image' | 'action';
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
  wrap?: string;
  color?: string;
  variant?: string;
  icon?: string;
  size?: number;
  rounded?: boolean;
  actions?: Array<{
    label: string;
    icon?: string;
    color?: string;
    onClick: (record: any) => void;
  }>;
}

export interface TableFilter {
  key: string;
  label: string;
  type: 'search' | 'select' | 'date_range' | 'number_range';
  options?: Array<{ value: string; label: string }>;
  placeholder?: string;
  multiple?: boolean;
}

export interface TableConfig {
  columns: TableColumn[];
  filters: TableFilter[];
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

export interface TableResult {
  config: TableConfig;
  data: any[];
  pagination: TablePagination;
  filters: Record<string, any>;
  sort: Record<string, 'asc' | 'desc'>;
  search: string | null;
}

export interface TableProps {
  result: TableResult;
  onSearch?: (query: string) => void;
  onFilter?: (filters: Record<string, any>) => void;
  onSort?: (column: string, direction: 'asc' | 'desc') => void;
  onPageChange?: (page: number) => void;
  className?: string;
}
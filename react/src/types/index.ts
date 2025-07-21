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
  wrap?: string;
  color?: string;
  variant?: string;
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

export interface TableResult {
  config: TableConfig;
  data: any[];
  pagination: TablePagination;
  sort: Record<string, 'asc' | 'desc'>;
  search: string | null;
}

export interface TableProps {
  result: TableResult;
  onSearch?: (query: string) => void;
  onSort?: (column: string, direction: 'asc' | 'desc') => void;
  onPageChange?: (page: number) => void;
  className?: string;
}
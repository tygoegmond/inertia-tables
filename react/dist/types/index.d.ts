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
export interface TableAction {
    name: string;
    label: string;
    icon?: string;
    color: string;
    size: string;
    style: 'button' | 'link' | 'iconButton';
    outlined: boolean;
    tooltip?: string;
    badge?: string;
    badgeColor?: string;
    extraAttributes?: Record<string, any>;
    disabled?: boolean;
    hidden?: boolean;
    hasAction?: boolean;
    hasUrl?: boolean;
    url?: string;
    openUrlInNewTab?: boolean;
    requiresConfirmation?: boolean;
    confirmationTitle?: string;
    confirmationMessage?: string;
    confirmationButton?: string;
    cancelButton?: string;
}
export interface TableBulkAction {
    name: string;
    label: string;
    icon?: string;
    color: string;
    size: string;
    style: 'button' | 'link' | 'iconButton';
    outlined: boolean;
    tooltip?: string;
    badge?: string;
    badgeColor?: string;
    extraAttributes?: Record<string, any>;
    disabled?: boolean;
    hidden?: boolean;
    hasAction?: boolean;
    requiresConfirmation?: boolean;
    confirmationTitle?: string;
    confirmationMessage?: string;
    confirmationButton?: string;
    cancelButton?: string;
    deselectRecordsAfterCompletion?: boolean;
    type: 'bulk';
}
export interface TableActionGroup {
    type: 'group';
    name: string;
    label: string;
    icon?: string;
    color: string;
    size: string;
    style: 'button' | 'link' | 'iconButton';
    outlined: boolean;
    tooltip?: string;
    extraAttributes?: Record<string, any>;
    actions: TableAction[];
}
export type TableActionItem = TableAction | TableActionGroup;
export type TableBulkActionItem = TableBulkAction | {
    type: 'group';
    actions: TableBulkAction[];
    [key: string]: any;
};
export type TableHeaderActionItem = TableAction | TableActionGroup;
export interface TableResult<T = any> {
    config: TableConfig;
    data: T[];
    pagination: TablePagination;
    sort: Record<string, 'asc' | 'desc'>;
    search: string | null;
    name?: string | null;
    actions?: TableActionItem[];
    bulkActions?: TableBulkActionItem[];
    headerActions?: TableHeaderActionItem[];
}
export interface TableProps<T = any> {
    state: TableResult<T> | undefined;
    className?: string;
}
export interface RequiredTableProps<T = any> {
    state: TableResult<T>;
    className?: string;
}
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

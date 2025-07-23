export { InertiaTable } from './components/InertiaTable';
export { DataTable } from './components/DataTable';
export { TableSearch } from './components/TableSearch';
export { TablePagination } from './components/TablePagination';
export { ErrorBoundary } from './components/ErrorBoundary';
export { DeferredTableLoader } from './components/DeferredTableLoader';
export { LoadingOverlay } from './components/LoadingOverlay';
export * from './components/columns';
export * from './components/table';
export * from './components/actions';
export * from './hooks';
export * from './types';
export * from './lib/actions';

// New enhanced data-table components
export { DataTable as EnhancedDataTable } from './components/data-table/data-table';
export { DataTableToolbar } from './components/data-table/data-table-toolbar';
export { DataTablePagination as EnhancedDataTablePagination } from './components/data-table/data-table-pagination';
export { DataTableColumnHeader } from './components/data-table/data-table-column-header';
export { DataTableFacetedFilter } from './components/data-table/data-table-faceted-filter';
export { DataTableViewOptions } from './components/data-table/data-table-view-options';
export { DataTableRowActions } from './components/data-table/data-table-row-actions';

// New UI components
export * from './components/ui/select';
export * from './components/ui/command';
export * from './components/ui/popover';
export * from './components/ui/separator';
export * from './components/ui/checkbox';

import { InertiaTable } from './components/InertiaTable';
export default InertiaTable;
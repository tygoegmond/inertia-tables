import * as React from "react";
import { TableResult } from "../types";
interface DataTableProps<T = any> {
    result: TableResult<T> | undefined;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
    className?: string;
    isLoading?: boolean;
    emptyMessage?: string;
    onRecordSelect?: (records: T[]) => void;
    onActionClick?: (action: any, record?: Record<string, any>) => void;
}
export declare const DataTable: React.NamedExoticComponent<DataTableProps<any>>;
export {};

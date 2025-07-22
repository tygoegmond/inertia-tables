import * as React from "react";
import { TableResult } from "../types";
interface DataTableProps {
    result: TableResult;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
    className?: string;
    isLoading?: boolean;
    emptyMessage?: string;
}
export declare const DataTable: React.NamedExoticComponent<DataTableProps>;
export {};

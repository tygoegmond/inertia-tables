import * as React from "react";
import { TableColumn } from "../../types";
interface SortableHeaderProps {
    column: TableColumn;
    sortDirection?: 'asc' | 'desc' | null;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
    className?: string;
}
export declare const SortableHeader: React.NamedExoticComponent<SortableHeaderProps>;
export {};

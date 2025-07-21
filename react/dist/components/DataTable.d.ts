import { TableResult } from "../types";
interface DataTableProps {
    result: TableResult;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
    className?: string;
}
export declare function DataTable({ result, onSort, className }: DataTableProps): import("react/jsx-runtime").JSX.Element;
export {};

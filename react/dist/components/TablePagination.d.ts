import { TablePagination as TablePaginationType } from "../types";
interface TablePaginationProps {
    pagination: TablePaginationType;
    onPageChange: (page: number) => void;
    className?: string;
}
export declare function TablePagination({ pagination, onPageChange, className }: TablePaginationProps): import("react/jsx-runtime").JSX.Element;
export {};

import * as React from "react";
import { TableResult } from "../types";
interface UseInertiaTableProps {
    initialSearch?: string;
    preserveState?: boolean;
    preserveScroll?: boolean;
    tableState?: TableResult;
}
interface InertiaTableState {
    searchValue: string;
    setSearchValue: React.Dispatch<React.SetStateAction<string>>;
    handleSearch: (query: string) => void;
    handleSort: (column: string, direction: 'asc' | 'desc') => void;
    handlePageChange: (page: number) => void;
    isNavigating: boolean;
}
export declare function useInertiaTable({ initialSearch, preserveState, preserveScroll, tableState, }?: UseInertiaTableProps): InertiaTableState;
export {};

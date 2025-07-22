import * as React from "react";
interface UseInertiaTableProps {
    initialSearch?: string;
    preserveState?: boolean;
    preserveScroll?: boolean;
}
interface InertiaTableState {
    searchValue: string;
    setSearchValue: React.Dispatch<React.SetStateAction<string>>;
    handleSearch: (query: string) => void;
    handleSort: (column: string, direction: 'asc' | 'desc') => void;
    handlePageChange: (page: number) => void;
    isNavigating: boolean;
}
export declare function useInertiaTable({ initialSearch, preserveState, preserveScroll, }?: UseInertiaTableProps): InertiaTableState;
export {};

import * as React from "react";
import { SortingState } from "@tanstack/react-table";
import { TableResult } from "../types";
interface UseTableStateProps {
    result: TableResult;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
}
interface TableState {
    sorting: SortingState;
    setSorting: React.Dispatch<React.SetStateAction<SortingState>>;
    handleSort: (column: string, direction: 'asc' | 'desc') => void;
    isLoading: boolean;
    error: Error | null;
}
export declare function useTableState({ result, onSort }: UseTableStateProps): TableState;
export {};

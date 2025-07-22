import * as React from "react";
import { ColumnDef } from "@tanstack/react-table";
import { TableResult, TableColumn, TableAction } from "../types";
interface UseTableColumnsProps {
    result: TableResult | undefined;
    renderCell?: (column: TableColumn, value: any, record: any) => React.ReactNode;
    onRecordSelect?: (records: any[]) => void;
    onActionClick?: (action: TableAction, record?: Record<string, any>) => void;
}
interface TableColumnsState {
    columns: ColumnDef<any>[];
    visibleColumns: TableColumn[];
    error: Error | null;
}
export declare function useTableColumns({ result, renderCell, onRecordSelect, onActionClick, }: UseTableColumnsProps): TableColumnsState;
export {};

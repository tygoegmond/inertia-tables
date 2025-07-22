import * as React from "react";
import { ColumnDef } from "@tanstack/react-table";
import { TableResult, TableColumn } from "../types";
interface UseTableColumnsProps {
    result: TableResult | undefined;
    renderCell?: (column: TableColumn, value: any, record: any) => React.ReactNode;
}
interface TableColumnsState {
    columns: ColumnDef<any>[];
    visibleColumns: TableColumn[];
    error: Error | null;
}
export declare function useTableColumns({ result, renderCell, }: UseTableColumnsProps): TableColumnsState;
export {};

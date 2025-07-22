import * as React from "react";
import { Row } from "@tanstack/react-table";
interface TableBodyComponentProps {
    rows: Row<any>[];
    columnsCount: number;
    isLoading?: boolean;
    emptyMessage?: string;
}
export declare const TableBodyComponent: React.NamedExoticComponent<TableBodyComponentProps>;
export {};

import * as React from "react";
import { HeaderGroup } from "@tanstack/react-table";
import { TableResult } from "../../types";
interface TableHeaderComponentProps<T = unknown> {
    headerGroups: HeaderGroup<T>[];
    result: TableResult<T>;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
}
export declare const TableHeaderComponent: React.NamedExoticComponent<TableHeaderComponentProps<unknown>>;
export {};

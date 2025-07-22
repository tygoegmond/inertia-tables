import * as React from "react";
import type { TableAction, TableActionItem } from "../../types";
interface ActionsColumnProps {
    actions: TableActionItem[];
    record: Record<string, any>;
    onActionClick: (action: TableAction, record: Record<string, any>) => void;
    className?: string;
}
export declare const ActionsColumn: React.FC<ActionsColumnProps>;
export {};

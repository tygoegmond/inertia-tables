import * as React from "react";
import type { TableAction, TableHeaderActionItem } from "../../types";
interface HeaderActionsProps {
    headerActions: TableHeaderActionItem[];
    onActionClick: (action: TableAction) => void;
    className?: string;
}
export declare const HeaderActions: React.FC<HeaderActionsProps>;
export {};

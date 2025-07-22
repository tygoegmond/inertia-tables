import * as React from "react";
import type { TableBulkAction, TableBulkActionItem } from "../../types";
interface BulkActionsProps {
    bulkActions: TableBulkActionItem[];
    selectedRecords: Record<string, any>[];
    onBulkActionClick: (action: TableBulkAction, records: Record<string, any>[]) => void;
    className?: string;
}
export declare const BulkActions: React.FC<BulkActionsProps>;
export {};

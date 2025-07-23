import { TableAction, TableRowData } from '../types';

export interface MergedAction extends TableAction {
  actionUrl?: string;
  disabled?: boolean;
  openUrlInNewTab?: boolean;
}

/**
 * Merges static action configuration with row-specific action data
 */
export function mergeActionWithRowData(
  staticAction: TableAction,
  rowData: TableRowData
): MergedAction {
  const rowActionData = rowData.actions?.[staticAction.name] || {};

  return {
    ...staticAction,
    ...rowActionData,
  };
}

/**
 * Gets all available actions for a specific row with merged data
 * Note: Backend already filters out unauthorized and hidden actions
 * Only actions present in rowData.actions are considered available
 */
export function getRowActions(
  staticActions: TableAction[] = [],
  rowData: TableRowData
): MergedAction[] {
  return staticActions
    .filter((action) => rowData.actions?.[action.name] !== undefined)
    .map((action) => mergeActionWithRowData(action, rowData));
}

/**
 * Gets a specific action for a row with merged data
 */
export function getRowAction(
  actionName: string,
  staticActions: TableAction[] = [],
  rowData: TableRowData
): MergedAction | undefined {
  const staticAction = staticActions.find(
    (action) => action.name === actionName
  );
  if (!staticAction) {
    return undefined;
  }

  return mergeActionWithRowData(staticAction, rowData);
}

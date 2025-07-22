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

export function useTableState({ result, onSort }: UseTableStateProps): TableState {
  const [sorting, setSorting] = React.useState<SortingState>([]);
  const [isLoading, setIsLoading] = React.useState(false);
  const [error, setError] = React.useState<Error | null>(null);

  const handleSort = React.useCallback(
    (column: string, direction: 'asc' | 'desc') => {
      try {
        setError(null);
        setIsLoading(true);
        onSort?.(column, direction);
      } catch (err) {
        setError(err instanceof Error ? err : new Error('Sort operation failed'));
      } finally {
        setIsLoading(false);
      }
    },
    [onSort]
  );

  // Sync sorting state with result
  React.useEffect(() => {
    if (result.sort) {
      const newSorting: SortingState = Object.entries(result.sort).map(([id, desc]) => ({
        id,
        desc: desc === 'desc',
      }));
      setSorting(newSorting);
    }
  }, [result.sort]);

  return {
    sorting,
    setSorting,
    handleSort,
    isLoading,
    error,
  };
}
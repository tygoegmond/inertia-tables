import * as React from 'react';
import { router, usePage } from '@inertiajs/react';
import { TableResult } from '../types';

interface UseInertiaTableProps {
  initialSearch?: string;
  preserveState?: boolean;
  preserveScroll?: boolean;
  tableState?: TableResult;
}

interface InertiaTableState {
  searchValue: string;
  setSearchValue: React.Dispatch<React.SetStateAction<string>>;
  handleSearch: (query: string) => void;
  handleSort: (column: string | null, direction: 'asc' | 'desc' | null) => void;
  handlePageChange: (page: number) => void;
  isNavigating: boolean;
}

export function useInertiaTable({
  initialSearch = '',
  preserveState = true,
  preserveScroll = true,
  tableState,
}: UseInertiaTableProps = {}): InertiaTableState {
  const [searchValue, setSearchValue] = React.useState(initialSearch);
  const [isNavigating, setIsNavigating] = React.useState(false);
  const pendingRequestsRef = React.useRef(0);
  const { props } = usePage();

  // Auto-detect table name and prop name
  const tableName = tableState?.name;
  const propName = React.useMemo(() => {
    if (!tableState || !tableName) return null;

    // Find which prop contains this table state by matching the table name
    for (const [key, value] of Object.entries(props)) {
      if (
        value &&
        typeof value === 'object' &&
        'name' in value &&
        value.name === tableName
      ) {
        return key;
      }
    }
    return null;
  }, [props, tableState, tableName]);

  const navigate = React.useCallback(
    (params: Record<string, any>) => {
      pendingRequestsRef.current++;
      setIsNavigating(true);

      // Table name is always required now
      if (!tableName) {
        console.error('Table name is required for navigation');
        pendingRequestsRef.current--;
        if (pendingRequestsRef.current === 0) {
          setIsNavigating(false);
        }
        return;
      }

      // Get current URL parameters to preserve other table states
      const currentUrl = new URL(window.location.href);
      const currentParams: Record<string, any> = {};

      // Parse existing query parameters
      for (const [key, value] of currentUrl.searchParams.entries()) {
        // Handle nested parameters like "users[search]"
        const match = key.match(/^([^[]+)\[([^]]+)\]$/);
        if (match) {
          const [, tableKey, paramKey] = match;
          if (!currentParams[tableKey]) {
            currentParams[tableKey] = {};
          }
          currentParams[tableKey][paramKey] = value;
        } else {
          currentParams[key] = value;
        }
      }

      // Update only this table's parameters, merging with existing table params
      const finalParams = {
        ...currentParams,
        [tableName]: {
          ...(currentParams[tableName] || {}),
          ...params,
        },
      };

      const options: any = {
        preserveState,
        preserveScroll,
        onFinish: () => {
          pendingRequestsRef.current--;
          if (pendingRequestsRef.current === 0) {
            setIsNavigating(false);
          }
        },
        onError: () => {
          pendingRequestsRef.current--;
          if (pendingRequestsRef.current === 0) {
            setIsNavigating(false);
          }
        },
      };

      // Add partial reload if we know the prop name
      if (propName) {
        options.only = [propName];
      }

      router.get(window.location.pathname, finalParams, options);
    },
    [preserveState, preserveScroll, tableName, propName]
  );

  const handleSearch = React.useCallback(
    (query: string) => {
      setSearchValue(query);
      navigate({ search: query });
    },
    [navigate]
  );

  const handleSort = React.useCallback(
    (column: string | null, direction: 'asc' | 'desc' | null) => {
      if (column === null || direction === null) {
        // Clear sorting
        navigate({ sort: undefined, direction: undefined });
      } else {
        navigate({ sort: column, direction });
      }
    },
    [navigate]
  );

  const handlePageChange = React.useCallback(
    (page: number) => {
      navigate({ page });
    },
    [navigate]
  );

  return {
    searchValue,
    setSearchValue,
    handleSearch,
    handleSort,
    handlePageChange,
    isNavigating,
  };
}

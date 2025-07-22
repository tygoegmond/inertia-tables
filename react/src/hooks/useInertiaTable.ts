import * as React from "react";
import { router } from "@inertiajs/react";

interface UseInertiaTableProps {
  initialSearch?: string;
  preserveState?: boolean;
  preserveScroll?: boolean;
}

interface InertiaTableState {
  searchValue: string;
  setSearchValue: React.Dispatch<React.SetStateAction<string>>;
  handleSearch: (query: string) => void;
  handleSort: (column: string, direction: 'asc' | 'desc') => void;
  handlePageChange: (page: number) => void;
  isNavigating: boolean;
}

export function useInertiaTable({
  initialSearch = '',
  preserveState = true,
  preserveScroll = true,
}: UseInertiaTableProps = {}): InertiaTableState {
  const [searchValue, setSearchValue] = React.useState(initialSearch);
  const [isNavigating, setIsNavigating] = React.useState(false);

  const navigate = React.useCallback(
    (params: Record<string, any>) => {
      setIsNavigating(true);
      
      router.get(
        window.location.pathname,
        params,
        {
          preserveState,
          preserveScroll,
          onFinish: () => setIsNavigating(false),
          onError: () => setIsNavigating(false),
        }
      );
    },
    [preserveState, preserveScroll]
  );

  const handleSearch = React.useCallback(
    (query: string) => {
      setSearchValue(query);
      navigate({ search: query });
    },
    [navigate]
  );

  const handleSort = React.useCallback(
    (column: string, direction: 'asc' | 'desc') => {
      navigate({ sort: column, direction });
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
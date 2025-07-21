import * as React from "react";
import { router } from "@inertiajs/react";
import { TableProps } from "../types";
import { DataTable } from "./DataTable";
import { TableSearch } from "./TableSearch";
import { TablePagination } from "./TablePagination";

export function InertiaTable({ state, className }: TableProps) {
  const [searchValue, setSearchValue] = React.useState(state.search || '');

  const handleSearch = (query: string) => {
    setSearchValue(query);
    router.get(window.location.pathname, { search: query }, { 
      preserveState: true, 
      preserveScroll: true 
    });
  };

  const handleSort = (column: string, direction: 'asc' | 'desc') => {
    router.get(window.location.pathname, { sort: column, direction }, { 
      preserveState: true, 
      preserveScroll: true 
    });
  };

  const handlePageChange = (page: number) => {
    router.get(window.location.pathname, { page }, { 
      preserveState: true, 
      preserveScroll: true 
    });
  };

  return (
    <div className={`space-y-4 ${className}`}>
      {state.config?.searchable && (
        <TableSearch
          value={searchValue}
          onChange={handleSearch}
          placeholder="Search..."
          className="max-w-sm"
        />
      )}

      <DataTable
        result={state}
        onSort={handleSort}
      />

      <TablePagination
        pagination={state.pagination}
        onPageChange={handlePageChange}
      />
    </div>
  );
}

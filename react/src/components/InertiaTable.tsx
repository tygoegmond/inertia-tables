import * as React from "react";
import { TableProps } from "../types";
import { DataTable } from "./DataTable";
import { TableSearch } from "./TableSearch";
import { TablePagination } from "./TablePagination";

export function InertiaTable({ result, onSearch, onSort, onPageChange, className }: TableProps) {
  const [searchValue, setSearchValue] = React.useState(result.search || '');

  const handleSearchChange = (value: string) => {
    setSearchValue(value);
    if (onSearch) {
      onSearch(value);
    }
  };

  return (
    <div className={`space-y-4 ${className}`}>
      {result.config?.searchable && (
        <TableSearch
          value={searchValue}
          onChange={handleSearchChange}
          placeholder="Search..."
          className="max-w-sm"
        />
      )}
      
      <DataTable
        result={result}
        onSort={onSort}
      />
      
      <TablePagination
        pagination={result.pagination}
        onPageChange={onPageChange || (() => {})}
      />
    </div>
  );
}
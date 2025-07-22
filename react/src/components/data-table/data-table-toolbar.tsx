"use client"

import { Table } from "@tanstack/react-table"
import { X } from "lucide-react"

import { Button } from "../ui/button"
import { Input } from "../ui/input"
import { DataTableViewOptions } from "./data-table-view-options"
import { DataTableFacetedFilter } from "./data-table-faceted-filter"

interface DataTableToolbarProps<TData> {
  table: Table<TData>
  searchKey?: string
  searchPlaceholder?: string
  filters?: Array<{
    column: string
    title: string
    options: Array<{
      label: string
      value: string
      icon?: React.ComponentType<{ className?: string }>
    }>
  }>
  headerActions?: React.ReactNode
}

export function DataTableToolbar<TData>({
  table,
  searchKey = "title",
  searchPlaceholder = "Search...",
  filters = [],
  headerActions,
}: DataTableToolbarProps<TData>) {
  const isFiltered = table.getState().columnFilters.length > 0

  return (
    <div className="flex items-center justify-between">
      <div className="flex flex-1 items-center gap-2">
        <Input
          placeholder={searchPlaceholder}
          value={(table.getColumn(searchKey)?.getFilterValue() as string) ?? ""}
          onChange={(event) =>
            table.getColumn(searchKey)?.setFilterValue(event.target.value)
          }
          className="h-8 w-[150px] lg:w-[250px]"
        />
        {filters.map((filter) => {
          const column = table.getColumn(filter.column)
          return column ? (
            <DataTableFacetedFilter
              key={filter.column}
              column={column}
              title={filter.title}
              options={filter.options}
            />
          ) : null
        })}
        {isFiltered && (
          <Button
            variant="ghost"
            size="sm"
            onClick={() => table.resetColumnFilters()}
          >
            Reset
            <X />
          </Button>
        )}
      </div>
      <div className="flex items-center gap-2">
        <DataTableViewOptions table={table} />
        {headerActions}
      </div>
    </div>
  )
}
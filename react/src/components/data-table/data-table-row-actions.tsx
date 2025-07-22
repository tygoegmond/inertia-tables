"use client"

import { Row } from "@tanstack/react-table"
import { MoreHorizontal } from "lucide-react"

import { Button } from "../ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuShortcut,
  DropdownMenuTrigger,
} from "../ui/dropdown-menu"

interface DataTableRowActionsProps<TData> {
  row: Row<TData>
  actions?: Array<{
    label: string
    onClick: (data: TData) => void
    variant?: "default" | "destructive"
    shortcut?: string
  }>
}

export function DataTableRowActions<TData>({
  row,
  actions = [],
}: DataTableRowActionsProps<TData>) {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          variant="ghost"
          size="icon"
          className="data-[state=open]:bg-muted size-8"
        >
          <MoreHorizontal />
          <span className="sr-only">Open menu</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-[160px]">
        {actions.map((action, index) => (
          <div key={index}>
            <DropdownMenuItem
              onClick={() => action.onClick(row.original)}
              variant={action.variant}
            >
              {action.label}
              {action.shortcut && (
                <DropdownMenuShortcut>{action.shortcut}</DropdownMenuShortcut>
              )}
            </DropdownMenuItem>
            {action.variant === "destructive" && index < actions.length - 1 && (
              <DropdownMenuSeparator />
            )}
          </div>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
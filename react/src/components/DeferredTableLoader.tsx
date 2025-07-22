import * as React from "react";
import { Table } from "./ui/table";
import { TableBody, TableCell, TableHead, TableHeader, TableRow } from "./ui/table";

interface DeferredTableLoaderProps {
  className?: string;
  rows?: number;
  columns?: number;
}

export const DeferredTableLoader = React.memo<DeferredTableLoaderProps>(({ 
  className = "",
  rows = 5,
  columns = 4,
}) => {
  return (
    <div className={`relative rounded-md border ${className}`}>
      <Table>
        <TableHeader>
          <TableRow>
            {Array.from({ length: columns }).map((_, index) => (
              <TableHead key={index}>
                <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse" />
              </TableHead>
            ))}
          </TableRow>
        </TableHeader>
        <TableBody>
          {Array.from({ length: rows }).map((_, rowIndex) => (
            <TableRow key={rowIndex}>
              {Array.from({ length: columns }).map((_, colIndex) => (
                <TableCell key={colIndex}>
                  <div className="h-4 bg-gray-100 dark:bg-gray-800 rounded animate-pulse" />
                </TableCell>
              ))}
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <div className="absolute inset-0 flex items-center justify-center bg-white/70 dark:bg-black/70 rounded-md">
        <div className="flex items-center gap-2 bg-white dark:bg-black px-3 py-2 rounded-lg shadow-sm border border-gray-200 dark:border-white/20">
          <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600 dark:border-white" />
          <span className="text-sm text-gray-600 dark:text-white">Loading table data...</span>
        </div>
      </div>
    </div>
  );
});

DeferredTableLoader.displayName = "DeferredTableLoader";
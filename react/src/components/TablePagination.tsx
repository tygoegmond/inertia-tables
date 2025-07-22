import * as React from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { TablePagination as TablePaginationType } from "../types";

interface TablePaginationProps {
  pagination: TablePaginationType;
  onPageChange: (page: number) => void;
  className?: string;
}

export function TablePagination({ pagination, onPageChange, className }: TablePaginationProps) {
  const { current_page, last_page, from, to, total } = pagination;

  const getPageNumbers = () => {
    const pages = [];
    const maxVisible = 5;
    
    let start = Math.max(1, current_page - Math.floor(maxVisible / 2));
    const end = Math.min(last_page, start + maxVisible - 1);
    
    if (end - start + 1 < maxVisible) {
      start = Math.max(1, end - maxVisible + 1);
    }
    
    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
    
    return pages;
  };

  return (
    <div className={`flex items-center justify-between ${className}`}>
      <div className="text-sm text-muted-foreground">
        {from && to ? (
          `Showing ${from} to ${to} of ${total} results`
        ) : (
          `${total} results`
        )}
      </div>
      
      <div className="flex items-center gap-2">
        <button
          onClick={() => onPageChange(current_page - 1)}
          disabled={current_page <= 1}
          className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8"
        >
          <ChevronLeft className="h-4 w-4" />
        </button>
        
        {getPageNumbers().map((page) => (
          <button
            key={page}
            onClick={() => onPageChange(page)}
            className={`inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 w-8 ${
              page === current_page
                ? 'bg-primary text-primary-foreground'
                : 'border border-input bg-background hover:bg-accent hover:text-accent-foreground'
            }`}
          >
            {page}
          </button>
        ))}
        
        <button
          onClick={() => onPageChange(current_page + 1)}
          disabled={current_page >= last_page}
          className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8"
        >
          <ChevronRight className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
}
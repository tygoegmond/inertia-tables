import * as React from "react";
import { TableColumn } from "../../types";

interface BadgeColumnProps {
  column: TableColumn;
  value: any;
  record: any;
}

export function BadgeColumn({ column, value, record }: BadgeColumnProps) {
  if (value === null || value === undefined) {
    return <span className="text-muted-foreground">—</span>;
  }

  const getVariantClasses = (variant: string = 'default') => {
    const variants = {
      default: 'bg-secondary text-secondary-foreground',
      primary: 'bg-primary text-primary-foreground',
      secondary: 'bg-secondary text-secondary-foreground',
      destructive: 'bg-destructive text-destructive-foreground',
      outline: 'border border-input bg-background',
      success: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
      warning: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
      info: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    };
    return variants[variant as keyof typeof variants] || variants.default;
  };

  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${getVariantClasses(column.variant)}`}>
      {String(value)}
    </span>
  );
}
import { DataTable } from './data-table';
import { DataTableColumnHeader } from './data-table-column-header';
import { DataTableRowActions } from './data-table-row-actions';
import { Button } from '../ui/button';
import { Checkbox } from '../ui/checkbox';
import { Badge } from '../ui/badge';
import { ColumnDef } from '@tanstack/react-table';

// Example usage of the enhanced DataTable component
export interface Task {
  id: string;
  title: string;
  status: 'todo' | 'in-progress' | 'done' | 'canceled';
  priority: 'low' | 'medium' | 'high';
  label: string;
}

export const taskColumns: ColumnDef<Task>[] = [
  {
    id: 'select',
    header: ({ table }) => (
      <Checkbox
        checked={
          table.getIsAllPageRowsSelected() ||
          (table.getIsSomePageRowsSelected() && 'indeterminate')
        }
        onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
        aria-label="Select all"
      />
    ),
    cell: ({ row }) => (
      <Checkbox
        checked={row.getIsSelected()}
        onCheckedChange={(value) => row.toggleSelected(!!value)}
        aria-label="Select row"
      />
    ),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'title',
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Title" />
    ),
  },
  {
    accessorKey: 'status',
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Status" />
    ),
    cell: ({ row }) => {
      const status = row.getValue('status') as string;
      return (
        <Badge variant={status === 'done' ? 'default' : 'secondary'}>
          {status}
        </Badge>
      );
    },
  },
  {
    accessorKey: 'priority',
    header: ({ column }) => (
      <DataTableColumnHeader column={column} title="Priority" />
    ),
    cell: ({ row }) => {
      const priority = row.getValue('priority') as string;
      return (
        <Badge
          variant={
            priority === 'high'
              ? 'destructive'
              : priority === 'medium'
                ? 'default'
                : 'secondary'
          }
        >
          {priority}
        </Badge>
      );
    },
  },
  {
    id: 'actions',
    cell: ({ row }) => (
      <DataTableRowActions
        row={row}
        staticActions={[
          {
            name: 'edit',
            label: 'Edit',
            color: 'primary',
          },
          {
            name: 'delete',
            label: 'Delete',
            color: 'danger',
          },
        ]}
        onActionClick={(action, record) => console.log(action.name, record)}
      />
    ),
  },
];

const data: Task[] = [
  {
    id: '1',
    title: 'Implement authentication',
    status: 'todo',
    priority: 'high',
    label: 'feature',
  },
  {
    id: '2',
    title: 'Fix responsive design',
    status: 'in-progress',
    priority: 'medium',
    label: 'bug',
  },
  {
    id: '3',
    title: 'Update documentation',
    status: 'done',
    priority: 'low',
    label: 'docs',
  },
];

const statusOptions = [
  { label: 'Todo', value: 'todo' },
  { label: 'In Progress', value: 'in-progress' },
  { label: 'Done', value: 'done' },
  { label: 'Canceled', value: 'canceled' },
];

const priorityOptions = [
  { label: 'Low', value: 'low' },
  { label: 'Medium', value: 'medium' },
  { label: 'High', value: 'high' },
];

export function DataTableExample() {
  return (
    <DataTable
      columns={taskColumns}
      data={data}
      searchKey="title"
      searchPlaceholder="Filter tasks..."
      filters={[
        {
          column: 'status',
          title: 'Status',
          options: statusOptions,
        },
        {
          column: 'priority',
          title: 'Priority',
          options: priorityOptions,
        },
      ]}
      headerActions={<Button size="sm">Add Task</Button>}
    />
  );
}

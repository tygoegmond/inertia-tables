import { TableProps } from "../types";
declare const InertiaTableComponent: <T extends Record<string, any> = Record<string, any>>({ state, className }: TableProps<T>) => import("react/jsx-runtime").JSX.Element;
export declare const InertiaTable: typeof InertiaTableComponent;
export {};

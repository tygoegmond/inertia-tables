<?php

namespace Egmond\InertiaTables\Columns;

class ActionColumn extends BaseColumn
{
    protected string $type = 'action';

    protected array $actions = [];

    protected string $alignment = 'right';

    protected bool $dropdown = false;

    protected ?string $dropdownLabel = null;

    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function alignment(string $alignment): static
    {
        $this->alignment = $alignment;

        return $this;
    }

    public function dropdown(bool $dropdown = true, ?string $label = null): static
    {
        $this->dropdown = $dropdown;
        $this->dropdownLabel = $label ?? 'Actions';

        return $this;
    }

    public function formatValue(mixed $value, array $record): mixed
    {
        $formattedActions = [];

        foreach ($this->actions as $action) {
            $formattedActions[] = [
                'label' => $action['label'] ?? 'Action',
                'url' => $this->resolveUrl($action['url'] ?? '#', $record),
                'method' => $action['method'] ?? 'GET',
                'icon' => $action['icon'] ?? null,
                'color' => $action['color'] ?? 'primary',
                'size' => $action['size'] ?? 'sm',
                'variant' => $action['variant'] ?? 'outline',
                'confirm' => $action['confirm'] ?? null,
                'visible' => $this->resolveVisible($action['visible'] ?? true, $record),
            ];
        }

        return [
            'actions' => $formattedActions,
            'alignment' => $this->alignment,
            'dropdown' => $this->dropdown,
            'dropdownLabel' => $this->dropdownLabel,
        ];
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'actions' => $this->actions,
            'alignment' => $this->alignment,
            'dropdown' => $this->dropdown,
            'dropdownLabel' => $this->dropdownLabel,
        ]);
    }

    protected function resolveUrl(string $url, array $record): string
    {
        // Replace placeholders in URL with record values
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) use ($record) {
            return $record[$matches[1]] ?? $matches[0];
        }, $url);
    }

    protected function resolveVisible(mixed $visible, array $record): bool
    {
        if (is_callable($visible)) {
            return $visible($record);
        }

        return (bool) $visible;
    }
}

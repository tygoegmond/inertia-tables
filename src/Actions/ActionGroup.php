<?php

namespace Egmond\InertiaTables\Actions;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

class ActionGroup implements Arrayable
{
    use Concerns\HasLabel;
    use Concerns\HasIcon;
    use Concerns\HasColor;
    use Concerns\HasSize;
    use Concerns\CanBeHidden;
    use Concerns\CanBeDisabled;
    use Concerns\InteractsWithTable;

    protected string $name;

    protected array $actions = [];

    protected array $extraAttributes = [];

    protected ?string $tooltip = null;

    protected string $style = 'button'; // button, link, iconButton

    protected bool $outlined = false;

    public function __construct(string $name, array $actions = [])
    {
        $this->name = $name;
        $this->actions = $actions;
    }

    public static function make(string $name, array $actions = []): static
    {
        return new static($name, $actions);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function button(): static
    {
        $this->style = 'button';

        return $this;
    }

    public function link(): static
    {
        $this->style = 'link';

        return $this;
    }

    public function iconButton(): static
    {
        $this->style = 'iconButton';

        return $this;
    }

    public function outlined(bool $condition = true): static
    {
        $this->outlined = $condition;

        return $this;
    }

    public function tooltip(string $tooltip): static
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    public function extraAttributes(array $attributes): static
    {
        $this->extraAttributes = array_merge($this->extraAttributes, $attributes);

        return $this;
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    public function isOutlined(): bool
    {
        return $this->outlined;
    }

    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }

    public function getExtraAttributes(): array
    {
        return $this->extraAttributes;
    }

    public function getVisibleActions(?Model $record = null): array
    {
        return array_filter($this->actions, function ($action) use ($record) {
            if ($action instanceof Action || $action instanceof BulkAction) {
                return $action->isVisible($record) && $action->isAuthorized($record);
            }

            return true;
        });
    }

    public function hasVisibleActions(?Model $record = null): bool
    {
        return count($this->getVisibleActions($record)) > 0;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'size' => $this->getSize(),
            'style' => $this->style,
            'outlined' => $this->outlined,
            'tooltip' => $this->tooltip,
            'extraAttributes' => $this->extraAttributes,
            'actions' => array_map(fn ($action) => $action->toArray(), $this->actions),
            'type' => 'group',
        ];
    }
}
<?php

namespace Egmond\InertiaTables\Actions;

use Illuminate\Contracts\Support\Arrayable;

class Action implements Arrayable
{
    use Concerns\CanBeDisabled;
    use Concerns\CanBeHidden;
    use Concerns\GeneratesActionUrls;
    use Concerns\HasAction;
    use Concerns\HasAuthorization;
    use Concerns\HasColor;
    use Concerns\HasConfirmation;
    use Concerns\HasIcon;
    use Concerns\HasLabel;
    use Concerns\HasSize;
    use Concerns\HasUrl;
    use Concerns\InteractsWithTable;

    protected string $name;

    protected array $extraAttributes = [];

    protected ?string $tooltip = null;

    protected string $style = 'button'; // button, link, iconButton

    protected bool $outlined = false;

    protected ?string $badge = null;

    protected ?string $badgeColor = null;

    protected array $keyBindings = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function getName(): string
    {
        return $this->name;
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

    public function badge(string $badge, ?string $color = null): static
    {
        $this->badge = $badge;
        $this->badgeColor = $color;

        return $this;
    }

    public function keyBindings(array $bindings): static
    {
        $this->keyBindings = $bindings;

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

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function getBadgeColor(): ?string
    {
        return $this->badgeColor;
    }

    public function getKeyBindings(): array
    {
        return $this->keyBindings;
    }

    public function getExtraAttributes(): array
    {
        return $this->extraAttributes;
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'size' => $this->getSize(),
            'style' => $this->style,
            'outlined' => $this->outlined,
            'tooltip' => $this->tooltip,
            'badge' => $this->badge,
            'badgeColor' => $this->badgeColor,
            'keyBindings' => $this->keyBindings,
            'extraAttributes' => $this->extraAttributes,
            'requiresConfirmation' => $this->needsConfirmation(),
            'confirmationTitle' => $this->getConfirmationTitle(),
            'confirmationMessage' => $this->getConfirmationMessage(),
            'confirmationButton' => $this->getConfirmationButton(),
            'cancelButton' => $this->getCancelButton(),
            'openUrlInNewTab' => $this->shouldOpenUrlInNewTab(),
            'hasAction' => $this->hasAction(),
            'hasUrl' => $this->hasUrl(),
        ];

        // Add signed action URL for actions that need to be processed
        if ($this->hasAction()) {
            try {
                $data['actionUrl'] = $this->getActionUrl();
            } catch (\Exception $e) {
                // If we can't generate action URL, it will be handled on frontend
                $data['actionUrl'] = null;
            }
        }

        return $data;
    }
}

<?php

namespace Egmond\InertiaTables\Actions;

class Action
{
    protected string $name;

    protected string $label;

    protected ?string $icon = null;

    protected string $color = 'primary';

    protected ?string $url = null;

    protected bool $requiresConfirmation = false;

    protected ?string $confirmationTitle = null;

    protected ?string $confirmationText = null;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = $this->generateLabel($name);
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function color(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function requireConfirmation(
        ?string $title = null,
        ?string $text = null
    ): static {
        $this->requiresConfirmation = true;
        $this->confirmationTitle = $title;
        $this->confirmationText = $text;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function requiresConfirmation(): bool
    {
        return $this->requiresConfirmation;
    }

    public function getConfirmationTitle(): ?string
    {
        return $this->confirmationTitle;
    }

    public function getConfirmationText(): ?string
    {
        return $this->confirmationText;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'color' => $this->color,
            'url' => $this->url,
            'requiresConfirmation' => $this->requiresConfirmation,
            'confirmationTitle' => $this->confirmationTitle,
            'confirmationText' => $this->confirmationText,
        ];
    }

    protected function generateLabel(string $name): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $name));
    }
}

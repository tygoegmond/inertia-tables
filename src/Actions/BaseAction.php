<?php

namespace Egmond\InertiaTables\Actions;

use Illuminate\Contracts\Support\Arrayable;

abstract class BaseAction implements Arrayable
{
    use Concerns\CanBeDisabled;
    use Concerns\CanBeHidden;
    use Concerns\GeneratesActionUrls;
    use Concerns\HasAuthorization;
    use Concerns\HasColor;
    use Concerns\HasConfirmation;
    use Concerns\HasLabel;
    use Concerns\InteractsWithTable;

    protected string $name;

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

    public function toArray(): array
    {
        return array_merge([
            'name' => $this->name,
            'label' => $this->getLabel(),
            'color' => $this->getColor(),
            'requiresConfirmation' => $this->needsConfirmation(),
            'confirmationTitle' => $this->getConfirmationTitle(),
            'confirmationMessage' => $this->getConfirmationMessage(),
            'confirmationButton' => $this->getConfirmationButton(),
            'cancelButton' => $this->getCancelButton(),
            'hasAction' => $this->hasAction(),
            'actionUrl' => $this->getActionUrl(),
        ], $this->getAdditionalArrayData());
    }

    protected function getAdditionalArrayData(): array
    {
        return [];
    }

    abstract public function hasAction(): bool;
}

<?php

namespace Egmond\InertiaTables\Actions;

use Illuminate\Contracts\Support\Arrayable;

/** @phpstan-consistent-constructor */
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
        $data = [
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
        ];

        return $this->filterDefaults(array_merge($data, $this->getAdditionalArrayData()));
    }

    protected function filterDefaults(array $data): array
    {
        return array_filter($data, function ($value, $key) {
            // Always include required fields
            if (in_array($key, ['name', 'label', 'color'])) {
                return true;
            }
            
            // Filter out false, null, empty strings, and empty arrays
            return $value !== false && $value !== null && $value !== '' && $value !== [];
        }, ARRAY_FILTER_USE_BOTH);
    }

    protected function getAdditionalArrayData(): array
    {
        return [];
    }

    abstract public function hasAction(): bool;
}

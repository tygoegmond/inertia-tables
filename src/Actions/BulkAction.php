<?php

namespace Egmond\InertiaTables\Actions;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;

class BulkAction implements Arrayable
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

    protected ?Closure $action = null;

    protected bool $deselectRecordsAfterCompletion = false;

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

    public function action(Closure $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function hasAction(): bool
    {
        return $this->action !== null;
    }

    public function execute(Collection $records): mixed
    {
        if (! $this->action) {
            return null;
        }

        return $this->evaluate($this->action, ['records' => $records]);
    }

    public function deselectRecordsAfterCompletion(bool $condition = true): static
    {
        $this->deselectRecordsAfterCompletion = $condition;

        return $this;
    }

    public function shouldDeselectRecordsAfterCompletion(): bool
    {
        return $this->deselectRecordsAfterCompletion;
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
            'deselectRecordsAfterCompletion' => $this->deselectRecordsAfterCompletion,
            'type' => 'bulk',
        ];

        // Add signed action URL for bulk actions
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

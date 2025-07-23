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
    use Concerns\HasLabel;
    use Concerns\HasUrl;
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

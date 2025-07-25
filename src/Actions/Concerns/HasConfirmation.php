<?php

namespace Egmond\InertiaTables\Actions\Concerns;

trait HasConfirmation
{
    protected bool $requiresConfirmation = false;

    protected string $confirmationTitle = '';

    protected string $confirmationMessage = '';

    protected string $confirmationButton = '';

    protected string $cancelButton = '';

    public function requiresConfirmation(
        string $title = 'Confirm Action',
        string $message = 'Are you sure you want to perform this action?'
    ): static {
        $this->requiresConfirmation = true;
        $this->confirmationTitle = $title;
        $this->confirmationMessage = $message;

        return $this;
    }

    public function confirmationTitle(string $title): static
    {
        $this->confirmationTitle = $title;

        return $this;
    }

    public function confirmationMessage(string $message): static
    {
        $this->confirmationMessage = $message;

        return $this;
    }

    public function confirmationButton(string $button): static
    {
        $this->confirmationButton = $button;

        return $this;
    }

    public function cancelButton(string $button): static
    {
        $this->cancelButton = $button;

        return $this;
    }

    public function getConfirmationTitle(): string
    {
        return $this->confirmationTitle;
    }

    public function getConfirmationMessage(): string
    {
        return $this->confirmationMessage;
    }

    public function getConfirmationButton(): string
    {
        return $this->confirmationButton;
    }

    public function getCancelButton(): string
    {
        return $this->cancelButton;
    }

    public function needsConfirmation(): bool
    {
        return $this->requiresConfirmation;
    }
}

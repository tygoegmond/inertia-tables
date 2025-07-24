<?php

namespace Egmond\InertiaTables\Actions;

use Egmond\InertiaTables\Actions\Contracts\ActionContract;

/** @phpstan-consistent-constructor */
abstract class AbstractAction implements ActionContract
{
    use Concerns\CanBeDisabled;
    use Concerns\CanBeHidden;
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
}
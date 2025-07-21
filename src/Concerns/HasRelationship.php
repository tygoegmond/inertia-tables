<?php

namespace Egmond\InertiaTables\Concerns;

trait HasRelationship
{
    protected string|array|null $relationship = null;

    protected ?string $relationshipType = null;

    protected ?string $relationshipColumn = null;

    public function counts(string|array $relationship): static
    {
        $this->relationship = $relationship;
        $this->relationshipType = 'count';

        return $this;
    }

    public function exists(string|array $relationship): static
    {
        $this->relationship = $relationship;
        $this->relationshipType = 'exists';

        return $this;
    }

    public function avg(string|array $relationship, string $column): static
    {
        $this->relationship = $relationship;
        $this->relationshipType = 'avg';
        $this->relationshipColumn = $column;

        return $this;
    }

    public function max(string|array $relationship, string $column): static
    {
        $this->relationship = $relationship;
        $this->relationshipType = 'max';
        $this->relationshipColumn = $column;

        return $this;
    }

    public function min(string|array $relationship, string $column): static
    {
        $this->relationship = $relationship;
        $this->relationshipType = 'min';
        $this->relationshipColumn = $column;

        return $this;
    }

    public function sum(string|array $relationship, string $column): static
    {
        $this->relationship = $relationship;
        $this->relationshipType = 'sum';
        $this->relationshipColumn = $column;

        return $this;
    }

    public function getRelationship(): string|array|null
    {
        return $this->relationship;
    }

    public function getRelationshipType(): ?string
    {
        return $this->relationshipType;
    }

    public function getRelationshipColumn(): ?string
    {
        return $this->relationshipColumn;
    }

    public function hasRelationship(): bool
    {
        return $this->relationship !== null;
    }
}

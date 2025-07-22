<?php

namespace Egmond\InertiaTables\Actions\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait HasUrl
{
    protected ?Closure $url = null;

    protected bool $openUrlInNewTab = false;

    public function url(Closure $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function openUrlInNewTab(bool $condition = true): static
    {
        $this->openUrlInNewTab = $condition;

        return $this;
    }

    public function getUrl(?Model $record = null): ?string
    {
        if (! $this->url) {
            return null;
        }

        return $this->evaluate($this->url, ['record' => $record]);
    }

    public function hasUrl(): bool
    {
        return $this->url !== null;
    }

    public function shouldOpenUrlInNewTab(): bool
    {
        return $this->openUrlInNewTab;
    }
}
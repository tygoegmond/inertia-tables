<?php

namespace Egmond\InertiaTables\Http\Requests;

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Contracts\HasTable;
use Egmond\InertiaTables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\URL;

class ActionRequest extends FormRequest
{
    protected ?Table $table = null;

    protected Action|BulkAction|null $action = null;

    protected ?Collection $records = null;

    public function authorize(): bool
    {
        // Check if the URL signature is valid (for security)
        if (! URL::hasValidSignature($this)) {
            return false;
        }

        // Check if the action is authorized
        $action = $this->getAction();
        $records = $this->getRecords();

        // For regular actions, check authorization for each record
        if ($action instanceof Action) {
            return $records->every(fn ($record) => $action->isAuthorized($record));
        }

        // For bulk actions, check general authorization (no record parameter)
        return $action->isAuthorized();
    }

    public function rules(): array
    {
        return [
            'table' => ['required', 'string'],
            'name' => ['required', 'string'],
            'action' => ['required', 'string'],
            'records' => ['required', 'array'],
            'records.*' => ['required'],
        ];
    }

    public function getTable(): Table
    {
        if (! $this->table) {
            $tableClass = base64_decode($this->input('table'));

            if (! class_exists($tableClass)) {
                throw new \InvalidArgumentException("Table class {$tableClass} does not exist");
            }

            $tableInstance = app($tableClass);

            if ($tableInstance instanceof HasTable) {
                $this->table = $tableInstance->getTable();
            } else {
                throw new \InvalidArgumentException("Table class {$tableClass} must implement HasTable interface");
            }
        }

        return $this->table;
    }

    public function getAction(): Action|BulkAction
    {
        if (! $this->action) {
            $table = $this->getTable();
            $actionName = $this->input('name');
            $actionClass = base64_decode($this->input('action'));

            // Get all available actions
            $allActions = array_merge(
                $table->getActions(),
                $table->getBulkActions(),
                $table->getHeaderActions()
            );

            // Filter by class first then by name
            $action = collect($allActions)
                ->filter(fn ($a) => get_class($a) === $actionClass)
                ->first(fn ($a) => $a->getName() === $actionName);

            if (! $action) {
                // Debug information
                $availableActions = collect($allActions)->map(fn ($a) => $a->getName().' ('.get_class($a).')')->toArray();
                $actionClassInfo = $actionClass ? " of class {$actionClass}" : '';
                throw new \InvalidArgumentException("Action {$actionName}{$actionClassInfo} not found. Available actions: ".implode(', ', $availableActions));
            }

            $this->action = $action;
        }

        return $this->action;
    }

    public function getRecords(): Collection
    {
        if (! $this->records) {
            $table = $this->getTable();
            $recordIds = $this->input('records', []);

            // Get the model class from the table's query
            $query = $table->getQuery();
            $model = $query->getModel();

            // Fetch the actual records
            $this->records = $model->newQuery()
                ->whereIn($model->getKeyName(), $recordIds)
                ->get();
        }

        return $this->records;
    }
}

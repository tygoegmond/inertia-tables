<?php

namespace Egmond\InertiaTables\Http\Requests;

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
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

        if (method_exists($action, 'isAuthorized')) {
            // For regular actions, check authorization for each record
            if ($action instanceof Action) {
                return $records->every(fn ($record) => $action->isAuthorized($record));
            }

            // For bulk actions, check general authorization
            if ($action instanceof BulkAction) {
                return $action->isAuthorized();
            }
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'table' => ['required', 'string'],
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

            $this->table = app($tableClass);
        }

        return $this->table;
    }

    public function getAction(): Action|BulkAction
    {
        if (! $this->action) {
            $table = $this->getTable();
            $actionName = $this->input('action');

            // Search through all action types to find the matching action
            $allActions = array_merge(
                $table->getActions() ?? [],
                $table->getBulkActions() ?? [],
                $table->getHeaderActions() ?? []
            );

            $action = collect($allActions)->firstWhere('name', $actionName);

            if (! $action) {
                throw new \InvalidArgumentException("Action {$actionName} not found");
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

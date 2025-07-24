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

        $action = $this->getAction();

        // For regular actions, authorize the record from signed URL (secure)
        if ($action instanceof Action) {
            $record = $this->getRecord();

            return $action->isAuthorized($record);
        }

        // For bulk actions, check general authorization and each record from POST body
        if ($action instanceof BulkAction) {
            // First check general bulk action authorization
            if (! $action->isAuthorized()) {
                return false;
            }

            // Then check authorization for each record in the POST body
            $records = $this->getRecords();

            return $records->every(fn ($record) => $action->isAuthorized($record));
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'table' => ['required', 'string'],
            'name' => ['required', 'string'],
            'action' => ['required', 'string'],
            // records is optional in validation - specific requirements handled in getter methods
            'records' => ['sometimes', 'array'],
            'records.*' => ['required_with:records'],
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

            // Find action by both class and name in single pass
            $action = collect($allActions)->first(
                fn ($a) => get_class($a) === $actionClass && $a->getName() === $actionName
            );

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

    public function getRecord(): ?\Illuminate\Database\Eloquent\Model
    {
        $table = $this->getTable();
        $recordId = $this->query('record');

        if (! $recordId) {
            throw new \InvalidArgumentException('Record parameter is required for regular actions');
        }

        // Get the model class from the table's query
        $query = $table->getQuery();
        $model = $query->getModel();

        // Fetch the single record
        $record = $model->newQuery()
            ->where($model->getKeyName(), $recordId)
            ->first();

        if (! $record) {
            throw new \InvalidArgumentException("Record with ID {$recordId} not found");
        }

        return $record;
    }

    public function getRecords(): Collection
    {
        if (! $this->records) {
            $table = $this->getTable();
            $recordIds = $this->input('records', []);

            if (empty($recordIds)) {
                throw new \InvalidArgumentException('Records parameter is required for bulk actions');
            }

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

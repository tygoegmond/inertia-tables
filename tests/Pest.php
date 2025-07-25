<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Contracts\HasTable;
use Egmond\InertiaTables\Http\Requests\ActionRequest;
use Egmond\InertiaTables\Table;
use Egmond\InertiaTables\TableResult;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Egmond\InertiaTables\Tests\TestCase;
use Illuminate\Http\Request;

uses(TestCase::class)->in(__DIR__);

/**
 * Create an ActionRequest instance for testing
 */
function createActionRequest(array $data = []): ActionRequest
{
    // Separate query parameters (like 'record') from POST data (like 'records')
    $queryParams = [];
    $postData = [];

    foreach ($data as $key => $value) {
        if ($key === 'record') {
            $queryParams[$key] = $value;
        } else {
            $postData[$key] = $value;
        }
    }

    $request = Request::create('/test?'.http_build_query($queryParams), 'POST', $postData);

    return ActionRequest::createFrom($request);
}

/**
 * Create a basic table instance for testing
 */
function createBasicTable(): Table
{
    return new class extends Table implements HasTable
    {
        public function __construct()
        {
            $this->query(User::query())
                ->as('test_table')
                ->columns([])
                ->actions([
                    Action::make('edit')->authorize(fn () => true),
                    Action::make('delete')->authorize(fn () => true),
                ])
                ->bulkActions([
                    BulkAction::make('bulk_delete')->authorize(fn () => true),
                ]);
        }

        public function build(): TableResult
        {
            return parent::build();
        }

        public function getTable(): Table
        {
            return $this;
        }

        public function table(Table $table): Table
        {
            return $table;
        }

        public function toArray(): array
        {
            return [];
        }

        public function jsonSerialize(): mixed
        {
            return $this->toArray();
        }
    };
}

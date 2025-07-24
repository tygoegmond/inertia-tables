<?php

use Egmond\InertiaTables\Actions\Action;
use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Table;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

describe('Action HTTP Integration Tests', function () {
    
    beforeEach(function () {
        // Load the package routes
        loadPackageRoutes();
        
        $this->users = User::factory()->count(5)->create();
        
        // Create a test table class
        $this->table = new class extends Table {
            public function build() {
                return $this->query(User::query())
                    ->as('users')
                    ->columns([])
                    ->actions([
                        Action::make('edit')
                            ->authorize(fn() => true)
                            ->action(function($record, $params) {
                                return 'User ' . $record->name . ' edited successfully';
                            }),
                        Action::make('delete')
                            ->color('danger')
                            ->authorize(fn($record) => $record->status !== 'protected')
                            ->action(function($record, $params) {
                                $record->delete();
                                return 'User deleted successfully';
                            }),
                        Action::make('view')
                            ->authorize(fn() => true)
                            // No action defined - should skip execution
                    ])
                    ->bulkActions([
                        BulkAction::make('bulk_delete')
                            ->authorize(fn() => true)
                            ->action(function($records, $params) {
                                $count = count($records);
                                foreach ($records as $record) {
                                    $record->delete();
                                }
                                return "Deleted {$count} users successfully";
                            }),
                        BulkAction::make('bulk_archive')
                            ->authorize(fn($record) => $record?->status === 'active')
                            ->action(function($records, $params) {
                                $count = 0;
                                foreach ($records as $record) {
                                    if ($record->status === 'active') {
                                        $record->update(['status' => 'archived']);
                                        $count++;
                                    }
                                }
                                return "Archived {$count} users successfully";
                            }),
                    ])
                    ->setTableClass(get_class($this))
                    ->build();
            }
        };
    });

    describe('Regular Action Execution', function () {
        
        it('can execute regular action via HTTP POST', function () {
            $user = $this->users->first();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response->assertStatus(302); // Redirect response
            $response->assertRedirect(); // Should redirect somewhere
        });

        it('returns JSON response when expecting JSON', function () {
            $user = $this->users->first();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response = $this->postJson($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
            ]);
            $response->assertJsonStructure([
                'success',
                'redirect',
                'message',
            ]);
        });

        it('skips execution when action has no logic', function () {
            $user = $this->users->first();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'view',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'view',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response->assertStatus(302);
            $response->assertRedirect();
        });

        it('executes action with custom parameters', function () {
            $user = $this->users->first();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
                'custom_field' => 'custom_value',
                'another_param' => 123,
            ]);
            
            $response->assertStatus(302);
        });

    });

    describe('Bulk Action Execution', function () {
        
        it('can execute bulk action via HTTP POST', function () {
            $userIds = $this->users->take(3)->pluck('id')->toArray();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => $userIds,
            ]);
            
            $response->assertStatus(302);
            
            // Verify users were actually deleted
            foreach ($userIds as $userId) {
                $this->assertDatabaseMissing('users', ['id' => $userId]);
            }
        });

        it('returns JSON response for bulk actions when expecting JSON', function () {
            $userIds = $this->users->take(2)->pluck('id')->toArray();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
            ]);
            
            $response = $this->postJson($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => $userIds,
            ]);
            
            $response->assertStatus(200);
            $response->assertJson(['success' => true]);
        });

        it('handles empty records array for bulk actions', function () {
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => [],
            ]);
            
            $response->assertStatus(302);
        });

        it('handles conditional authorization in bulk actions', function () {
            // Create users with different statuses
            $activeUsers = User::factory()->count(2)->create(['status' => 'active']);
            $inactiveUsers = User::factory()->count(2)->create(['status' => 'inactive']);
            
            $allUserIds = $activeUsers->concat($inactiveUsers)->pluck('id')->toArray();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_archive',
                'action' => base64_encode(BulkAction::class),
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_archive',
                'action' => base64_encode(BulkAction::class),
                'records' => $allUserIds,
            ]);
            
            $response->assertStatus(302);
            
            // Only active users should be archived
            foreach ($activeUsers as $user) {
                $this->assertDatabaseHas('users', [
                    'id' => $user->id,
                    'status' => 'archived'
                ]);
            }
            
            // Inactive users should remain unchanged
            foreach ($inactiveUsers as $user) {
                $this->assertDatabaseHas('users', [
                    'id' => $user->id,
                    'status' => 'inactive'
                ]);
            }
        });

    });

    describe('Request Validation and Security', function () {
        
        it('rejects requests without valid signature', function () {
            $user = $this->users->first();
            
            // Create URL without proper signature
            $response = $this->post(route('inertia-tables.action'), [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response->assertStatus(403); // Forbidden due to invalid signature
        });

        it('validates required fields', function () {
            $signedUrl = generateSignedActionUrl([]);
            
            $response = $this->post($signedUrl, []); // Empty request
            
            $response->assertStatus(422); // Validation error
            $response->assertJsonValidationErrors(['table', 'name', 'action']);
        });

        it('validates table parameter as string', function () {
            $signedUrl = generateSignedActionUrl([
                'table' => 123, // Invalid - should be string
                'name' => 'edit',
                'action' => base64_encode(Action::class),
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => 123,
                'name' => 'edit',
                'action' => base64_encode(Action::class),
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['table']);
        });

        it('validates records parameter as array when present', function () {
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'bulk_delete',
                'action' => base64_encode(BulkAction::class),
                'records' => 'not-an-array', // Invalid
            ]);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['records']);
        });

    });

    describe('Error Handling', function () {
        
        it('handles non-existent table class gracefully', function () {
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode('NonExistentTableClass'),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode('NonExistentTableClass'),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
            ]);
            
            $response->assertStatus(500); // Server error due to class not found
        });

        it('handles non-existent action gracefully', function () {
            $user = $this->users->first();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'nonexistent_action',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'nonexistent_action',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response->assertStatus(500); // Server error due to action not found
        });

        it('handles non-existent record gracefully', function () {
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => 99999, // Non-existent record
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => 99999,
            ]);
            
            $response->assertStatus(302); // Should still process but with null record
        });

    });

    describe('Middleware Integration', function () {
        
        it('applies web middleware to action route', function () {
            // This test verifies that the route has proper middleware applied
            $route = Route::getRoutes()->getByName('inertia-tables.action');
            
            expect($route)->not->toBeNull();
            expect($route->middleware())->toContain('web');
            expect($route->middleware())->toContain('signed');
        });

        it('requires signed URLs through middleware', function () {
            $user = $this->users->first();
            
            // Make request to unsigned URL
            $response = $this->post(route('inertia-tables.action'), [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response->assertStatus(403); // Forbidden by signed middleware
        });

    });

    describe('Inertia.js Integration', function () {
        
        it('handles Inertia requests properly', function () {
            $user = $this->users->first();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ], [
                'X-Inertia' => 'true',
                'Accept' => 'application/json',
            ]);
            
            // Should return redirect response even with JSON accept header when X-Inertia is present
            $response->assertStatus(302);
        });

        it('returns JSON when expecting JSON without Inertia header', function () {
            $user = $this->users->first();
            
            $signedUrl = generateSignedActionUrl([
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ]);
            
            $response = $this->post($signedUrl, [
                'table' => base64_encode(get_class($this->table)),
                'name' => 'edit',
                'action' => base64_encode(Action::class),
                'record' => $user->id,
            ], [
                'Accept' => 'application/json',
                // No X-Inertia header
            ]);
            
            $response->assertStatus(200);
            $response->assertJson(['success' => true]);
        });

    });

});

// Helper method to load package routes
function loadPackageRoutes(): void
{
    require_once __DIR__ . '/../../routes/web.php';
}

// Helper method to generate signed action URLs
function generateSignedActionUrl(array $params): string
{
    return URL::signedRoute('inertia-tables.action', $params);
}
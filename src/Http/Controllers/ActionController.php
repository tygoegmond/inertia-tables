<?php

namespace Egmond\InertiaTables\Http\Controllers;

use Egmond\InertiaTables\Actions\BulkAction;
use Egmond\InertiaTables\Http\Requests\ActionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ActionController extends Controller
{
    public function __invoke(ActionRequest $request): JsonResponse|RedirectResponse
    {
        $action = $request->getAction();

        // Execute the action only if it has action logic
        if ($action->hasAction()) {
            $result = $this->executeAction($request, $action);
        }

        // Handle redirects
        if (isset($result) && $result instanceof RedirectResponse) {
            return $this->handleResponse($request, $result);
        }

        // Default redirect back
        return $this->handleResponse($request, back());
    }

    private function executeAction(ActionRequest $request, $action)
    {
        if ($action instanceof BulkAction) {
            // BulkActions: Use records from POST body (requires authorization)
            $records = $request->getRecords();
            return $action->execute($records);
        } else {
            // Regular Actions: Use single record from signed URL (secure)
            $record = $request->getRecord();
            return $action->execute($record);
        }
    }

    protected function handleResponse(ActionRequest $request, RedirectResponse $response): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'redirect_url' => $response->getTargetUrl(),
                'message' => session()->get('success') ?? session()->get('status'),
            ]);
        }

        return $response;
    }
}

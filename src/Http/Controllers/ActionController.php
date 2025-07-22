<?php

namespace Egmond\InertiaTables\Http\Controllers;

use Egmond\InertiaTables\Http\Requests\ActionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ActionController extends Controller
{
    public function __invoke(ActionRequest $request): JsonResponse|RedirectResponse
    {
        $table = $request->getTable();
        $action = $request->getAction();
        $records = $request->getRecords();

        // Execute the action
        if ($action->hasAction()) {
            if (method_exists($action, 'execute')) {
                // Regular action with single record or BulkAction with collection
                $result = $records->count() === 1 && ! ($action instanceof \Egmond\InertiaTables\Actions\BulkAction)
                    ? $action->execute($records->first())
                    : $action->execute($records);
            }
        }

        // Handle redirects
        if (isset($result) && $result instanceof RedirectResponse) {
            return $this->handleResponse($request, $result);
        }

        // Default redirect back
        return $this->handleResponse($request, back());
    }

    protected function handleResponse(ActionRequest $request, RedirectResponse $response): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'redirect_url' => $response->getTargetUrl(),
                'message' => session()->get('success') ?? session()->get('status'),
            ]);
        }

        return $response;
    }
}

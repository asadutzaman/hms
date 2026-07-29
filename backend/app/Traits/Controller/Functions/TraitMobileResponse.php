<?php

namespace App\Traits\Controller\Functions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Consistent response envelope for the mobile (/api/v1) API.
 *
 * Unlike TraitRestResponse (which returns a bare payload for the SPA), every
 * mobile response is wrapped as { success, message, data, meta } so the app
 * can rely on a single shape. Errors continue to flow through
 * App\Exceptions\Handler when controllers throw; mobileError() is for
 * explicit, non-exceptional error payloads.
 */
trait TraitMobileResponse
{
    protected function mobileSuccess($data = null, string $message = 'OK', array $meta = [], int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => (object) $meta,
        ], $code);
    }

    protected function mobileError(string $message = 'Something went wrong', int $code = 422, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Wrap a Laravel paginator into the mobile envelope, exposing pagination
     * under meta and the rows under data. Optionally pass already-transformed
     * items (e.g. a resource collection) to use instead of the raw items.
     */
    protected function mobilePaginated(LengthAwarePaginator $paginator, $items = null, string $message = 'OK'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $items ?? $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'has_more'     => $paginator->hasMorePages(),
            ],
        ], 200);
    }
}

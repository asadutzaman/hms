<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * For the mobile API (/api/v1/*) we normalize every error into the same
     * { success:false, message, errors } envelope the mobile controllers use
     * on success, so the app has one shape to parse. We defer to the parent
     * (which invokes each custom exception's own render()) to get the correct
     * status + payload, then reshape it. Non-mobile routes are untouched.
     */
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if (!$request->is('api/v1/*')) {
            return $response;
        }

        $status  = method_exists($response, 'getStatusCode') ? ($response->getStatusCode() ?: 500) : 500;
        $payload = $response instanceof JsonResponse ? $response->getData(true) : null;

        $message = 'Request failed.';
        $errors  = null;

        if (is_string($payload) && $payload !== '') {
            $message = $payload;
        } elseif (is_array($payload)) {
            $errors = $payload;
            if (isset($payload['message']) && is_string($payload['message'])) {
                $message = $payload['message'];
            } else {
                // Validation-style ["field" => ["msg", ...]] — surface the first message.
                $first = collect($payload)->flatten()->first();
                if (is_string($first)) {
                    $message = $first;
                }
            }
        }

        if ($status >= 500) {
            $message = config('app.debug') ? ($e->getMessage() ?: $message) : 'Something went wrong on our end.';
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }
}

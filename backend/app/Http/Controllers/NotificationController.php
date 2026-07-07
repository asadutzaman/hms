<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Repositories\NotificationRepository;
use App\Services\SessionService;
use App\Traits\Controller\RestControllerTrait;
use Exception;

class NotificationController extends Controller
{
    private $repository;

    private $resource;

    use RestControllerTrait;

    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
        $this->resource = NotificationResource::class;
    }

    /** GET /notification/my — the current user's in-app notifications. */
    public function my()
    {
        try {
            $userId = (new SessionService())->init()->getUserId();
            $rows = $this->repository->forUser((int) $userId);
            $response = $this->resource::collection($rows)->toArray(request());
            return $this->successResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** GET /notification/unread-count */
    public function unreadCount()
    {
        try {
            $userId = (new SessionService())->init()->getUserId();
            return $this->successResponse(['count' => $this->repository->unreadCountForUser((int) $userId)]);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** PATCH /notification/{id}/read */
    public function markRead($id)
    {
        try {
            $notification = Notification::query()->findOrFail($id);
            $notification->is_read = true;
            $notification->read_at = now();
            $notification->save();
            $response = new $this->resource($notification, false);
            return $this->successResourceResponse($response);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    /** PATCH /notification/read-all */
    public function markAllRead()
    {
        try {
            $userId = (new SessionService())->init()->getUserId();
            Notification::query()
                ->where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
            return $this->successResponse(['message' => 'All notifications marked read.']);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}

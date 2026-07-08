<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Traits\Controller\Functions\TraitRestResponse;
use Exception;
use Illuminate\Http\Request;

/** Read-only staff view of online payment transactions, for reconciliation. */
class PaymentTransactionController extends Controller
{
    use TraitRestResponse;

    public function index(Request $request)
    {
        try {
            $query = PaymentTransaction::query()->orderByDesc('initiated_at');
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->input('payment_status'));
            }
            $rows = $query->limit(200)->get();
            return $this->successResponse($rows);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }
}

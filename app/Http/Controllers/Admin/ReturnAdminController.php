<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnModel;
use App\Services\StockLedgerService;
use App\Services\TransactionHubService;
use Illuminate\Http\Request;

class ReturnAdminController extends Controller
{
    public function index(TransactionHubService $hub)
    {
        $returns = ReturnModel::with(['order', 'user'])->latest()->paginate(25);

        return view('admin.returns', [
            'returns' => $returns,
            'summaries' => $hub->summaries(),
            'transactionTab' => 'returns',
        ]);
    }

    public function update(Request $request, ReturnModel $refund)
    {
        $request->validate([
            'status' => 'required|in:'.implode(',', ReturnModel::statusKeys()),
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $status = ReturnModel::normalizeStatus($request->input('status'));

        $prev = ReturnModel::normalizeStatus($refund->status);
        $refund->update([
            'status' => $status,
            'admin_note' => $request->admin_note,
        ]);
        $next = ReturnModel::normalizeStatus($refund->status);
        if (ReturnModel::restoresStock($next) && ! ReturnModel::restoresStock($prev)) {
            app(StockLedgerService::class)->restoreForApprovedReturn($refund->fresh(), $request->user()?->id);
        }

        return back()->with('status', __('Return updated.'));
    }
}

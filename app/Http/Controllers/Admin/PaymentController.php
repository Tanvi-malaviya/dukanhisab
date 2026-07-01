<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $paymentsQuery = Payment::with(['user', 'shop', 'plan']);

        // Filter status
        if ($request->filled('status')) {
            $paymentsQuery->where('status', $request->input('status'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $paymentsQuery->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('shop', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $paymentsQuery->latest()->paginate(10)->withQueryString();

        // Failed Payments counts
        $failedCount = Payment::where('status', 'failed')->count();
        // Successful Payments counts
        $successCount = Payment::where('status', 'successful')->count();
        // Total Refunded
        $refundedSum = Refund::where('status', 'successful')->sum('amount');

        $refunds = Refund::with('payment.shop')->latest()->paginate(10, ['*'], 'refunds_page');

        return view('admin.payments.index', compact('payments', 'refunds', 'failedCount', 'successCount', 'refundedSum'));
    }

    public function refund(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'successful') {
            return back()->with('error', 'Only successful payments can be refunded.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'status' => 'required|string|in:successful,pending,failed',
        ]);

        $status = $request->input('status', 'successful');

        // Process refund simulation
        $refund = Refund::create([
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'reason' => $request->input('reason'),
            'status' => $status,
            'refund_date' => now(),
        ]);

        if ($status === 'successful') {
            $payment->update(['status' => 'refunded']);

            // Update Shop's active subscription if needed (revert back to Free)
            $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
            if ($freePlan) {
                $payment->shop->update(['active_plan_id' => $freePlan->id]);
                // Revert active subscription statuses
                \App\Models\Subscription::where('shop_id', $payment->shop_id)
                    ->where('status', 'active')
                    ->update(['status' => 'expired']);
            }
        } elseif ($status === 'failed') {
            $payment->update(['status' => 'successful']);
        } else {
            // Pending/processing
            $payment->update(['status' => 'refunded']);
        }

        AuditLog::log("Processed refund #{$refund->id} (status: {$status}) for payment #{$payment->id} of shop '{$payment->shop->name}'", [
            'refund_id' => $refund->id,
            'amount' => $payment->amount,
            'status' => $status,
        ]);

        return back()->with('success', 'Payment refunded successfully.');
    }

    public function updateRefundStatus(Request $request, $id)
    {
        $refund = Refund::findOrFail($id);
        $payment = $refund->payment;

        $request->validate([
            'status' => 'required|string|in:successful,pending,failed',
        ]);

        $newStatus = $request->input('status');
        $oldStatus = $refund->status;

        if ($newStatus === $oldStatus) {
            return back()->with('info', 'Refund status is already ' . $newStatus);
        }

        // If transitioning to successful, demote shop subscription to free
        if ($newStatus === 'successful') {
            $payment->update(['status' => 'refunded']);

            $freePlan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
            if ($freePlan) {
                $payment->shop->update(['active_plan_id' => $freePlan->id]);
                \App\Models\Subscription::where('shop_id', $payment->shop_id)
                    ->where('status', 'active')
                    ->update(['status' => 'expired']);
            }
        } elseif ($newStatus === 'failed') {
            $payment->update(['status' => 'successful']);
        } else {
            // Pending/processing
            $payment->update(['status' => 'refunded']);
        }

        $refund->update([
            'status' => $newStatus,
        ]);

        AuditLog::log("Updated refund #{$refund->id} status from {$oldStatus} to {$newStatus}", [
            'refund_id' => $refund->id,
            'status' => $newStatus,
        ]);

        return back()->with('success', 'Refund status updated to ' . ucfirst($newStatus) . ' successfully.');
    }
}

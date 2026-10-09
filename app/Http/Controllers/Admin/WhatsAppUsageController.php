<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Shop;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppPackPurchase;
use App\Models\WhatsAppWallet as Wallet;
use App\Services\WhatsApp\InsufficientCreditsException;
use App\Services\WhatsApp\WhatsAppWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin → WhatsApp Usage: messages sent and their outcome, credit pack sales, per-shop balances,
 * and manual credit adjustments for support cases.
 */
class WhatsAppUsageController extends Controller
{
    private const PERIOD_DAYS = 30;

    public function index(Request $request)
    {
        $since = now()->subDays(self::PERIOD_DAYS);

        $statusCounts = WhatsAppMessageLog::where('created_at', '>=', $since)
            ->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        $totals = [
            'messages' => (int) $statusCounts->sum(),
            'delivered' => (int) (($statusCounts['delivered'] ?? 0) + ($statusCounts['read'] ?? 0)),
            'failed' => (int) ($statusCounts['failed'] ?? 0),
            'revenue' => (float) WhatsAppPackPurchase::where('status', 'paid')->where('paid_at', '>=', $since)->sum('amount'),
            'credits_sold' => (int) WhatsAppPackPurchase::where('status', 'paid')->where('paid_at', '>=', $since)->sum('credits'),
            'outstanding_credits' => (int) Wallet::sum('balance'),
        ];

        // Shops that ever used WhatsApp (have a wallet or sent a message).
        $sent = WhatsAppMessageLog::where('created_at', '>=', $since)
            ->select('shop_id', DB::raw('count(*) as sent'), DB::raw("sum(case when status = 'failed' then 1 else 0 end) as failed"))
            ->groupBy('shop_id');

        $shops = Shop::with('owner:id,name,email,mobile')
            ->leftJoin('whatsapp_wallets', 'whatsapp_wallets.shop_id', '=', 'shops.id')
            ->leftJoinSub($sent, 'sent', 'sent.shop_id', '=', 'shops.id')
            ->where(fn ($q) => $q->whereNotNull('whatsapp_wallets.id')->orWhereNotNull('sent.shop_id'))
            ->when($request->filled('search'), fn ($q) => $q->where('shops.name', 'like', '%' . $request->search . '%'))
            ->select('shops.id', 'shops.name', 'shops.owner_id',
                DB::raw('coalesce(whatsapp_wallets.balance, 0) as balance'),
                DB::raw('coalesce(sent.sent, 0) as sent'),
                DB::raw('coalesce(sent.failed, 0) as failed'))
            ->orderByDesc('sent')->orderBy('shops.name')
            ->paginate(20)->withQueryString();

        $purchases = WhatsAppPackPurchase::with('shop:id,name', 'user:id,name,email')
            ->where('status', 'paid')->latest('paid_at')->limit(20)->get();

        return view('admin.whatsapp.usage', [
            'totals' => $totals,
            'shops' => $shops,
            'purchases' => $purchases,
            'periodDays' => self::PERIOD_DAYS,
            'allShops' => Shop::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function adjust(Request $request, WhatsAppWallet $wallet)
    {
        $data = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'credits' => 'required|integer|not_in:0|between:-1000000,1000000',
            'note' => 'required|string|max:200',
        ]);

        $shop = Shop::findOrFail($data['shop_id']);

        try {
            $wallet->adjust($shop, (int) $data['credits'], $data['note'], auth('admin')->id());
        } catch (InsufficientCreditsException) {
            return back()->with('error', "{$shop->name} has only {$wallet->balance($shop->id)} credits — can't remove " . abs($data['credits']) . '.');
        }

        AuditLog::log('Adjusted WhatsApp credits', ['shop_id' => $shop->id, 'credits' => (int) $data['credits'], 'note' => $data['note']]);

        return back()->with('success', sprintf('%s%d credits for %s. New balance: %d.', $data['credits'] > 0 ? '+' : '', $data['credits'], $shop->name, $wallet->balance($shop->id)));
    }
}

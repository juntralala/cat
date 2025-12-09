<?php
namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $totalStockValue = Stock::sum('quantity');
        $lowStockItems = Stock::where('quantity', '<', 10)->count();
        $transactionsToday = Transaction::whereDate('transaction_date', today())->count();
        $transactionsThisMonth = Transaction::whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->count();

        // Recent Transactions (5 terbaru)
        $recentTransactions = Transaction::with(['recipient:id,name', 'transactionDetails'])
            ->latest('transaction_date')
            ->latest('created_at')
            ->take(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_date' => $transaction->transaction_date,
                    'type' => $transaction->type,
                    'party' => $transaction->type === 'in'
                        ? $transaction->supplier
                        : $transaction->recipient?->name,
                    'total_items' => $transaction->transactionDetails->count(),
                ];
            });

        // Top Items (barang dengan transaksi terbanyak)
        $topItems = Item::withCount([
            'transactionDetails' => function ($query) {
                $query->whereHas('transaction', function ($q) {
                    $q->whereMonth('transaction_date', now()->month)
                        ->whereYear('transaction_date', now()->year);
                });
            }
        ])
            ->having('transaction_details_count', '>', 0)
            ->orderBy('transaction_details_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'transaction_count' => $item->transaction_details_count,
                ];
            });

        // Monthly Transactions (bulan ini: in vs out)
        $monthlyTransactions = Transaction::select('type', DB::raw('count(*) as count'))
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'count' => $item->count,
                    'month' => now()->format('F Y'),
                ];
            });

        // Stock Alerts (stok menipis)
        $stockAlerts = Stock::with(['item:id,name', 'unit:id,name'])
            ->where('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get()
            ->map(function ($stock) {
                return [
                    'id' => $stock->id,
                    'item_name' => $stock->item->name,
                    'unit_name' => $stock->unit->name,
                    'quantity' => $stock->quantity,
                ];
            });

        // Top Divisions (divisi yang paling banyak mengambil barang)
        $topDivisions = Transaction::select('division')
            ->selectRaw('count(*) as transaction_count')
            ->where('type', 'out')
            ->whereNotNull('division')
            ->where('division', '!=', '')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->groupBy('division')
            ->orderBy('transaction_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                // Ambil total item yang diambil oleh divisi ini
                $totalItems = DB::table('transaction_details')
                    ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                    ->where('transactions.division', $item->division)
                    ->where('transactions.type', 'out')
                    ->whereMonth('transactions.transaction_date', now()->month)
                    ->whereYear('transactions.transaction_date', now()->year)
                    ->sum('transaction_details.quantity');

                // Ambil barang yang paling banyak diambil oleh divisi ini
                $topItem = DB::table('transaction_details')
                    ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                    ->join('items', 'transaction_details.item_id', '=', 'items.id')
                    ->select('items.name', DB::raw('sum(transaction_details.quantity) as total_quantity'))
                    ->where('transactions.division', $item->division)
                    ->where('transactions.type', 'out')
                    ->whereMonth('transactions.transaction_date', now()->month)
                    ->whereYear('transactions.transaction_date', now()->year)
                    ->groupBy('items.id', 'items.name')
                    ->orderBy('total_quantity', 'desc')
                    ->first();

                return [
                    'division' => $item->division,
                    'transaction_count' => $item->transaction_count,
                    'total_items' => $totalItems,
                    'top_item' => $topItem ? $topItem->name : '-',
                    'top_item_quantity' => $topItem ? $topItem->total_quantity : 0,
                ];
            });

        return Inertia::render('Home', [
            'totalItems' => $totalItems,
            'totalStockValue' => $totalStockValue,
            'lowStockItems' => $lowStockItems,
            'transactionsToday' => $transactionsToday,
            'transactionsThisMonth' => $transactionsThisMonth,
            'recentTransactions' => $recentTransactions,
            'topItems' => $topItems,
            'monthlyTransactions' => $monthlyTransactions,
            'stockAlerts' => $stockAlerts,
            'topDivisions' => $topDivisions,
        ]);
    }
}
<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionHistoryController extends Controller
{

    private function getTransactionQueryBuilderByRequest(Request $request)
    {
        $type = $request->input('type');
        $search = $request->input('search');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        return Transaction::with([
            'recipient:id,name,division',
            'transactionDetails.item:id,name',
            'transactionDetails.unit:id,name'
        ])->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier', 'like', "%{$search}%")
                    ->orWhere('division', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    // Cari di tabel recipient (relasi)
                    ->orWhereHas('recipient', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('division', 'like', "%{$search}%");
                    })
                    // Cari di tabel items melalui transaction_details (relasi nested)
                    ->orWhereHas('transactionDetails.item', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        })
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('transaction_date', [$startDate, $endDate]);
            })
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function index(Request $request)
    {
        $transactions = $this->getTransactionQueryBuilderByRequest($request)
            ->paginate(15)
            ->withQueryString();
        return Inertia::render('History', [
            'transactions' => $transactions
        ]);
    }

    public function toCSV(Request $request)
    {
        $transactions = $this->getTransactionQueryBuilderByRequest($request)
            ->get();

        $filename = 'transactions_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'Tanggal Transaksi',
                'Tipe',
                'Supplier/Penerima',
                'Divisi',
                'Nama Barang',
                'Satuan',
                'Jumlah',
                'Catatan'
            ]);

            foreach ($transactions as $transaction) {
                $party = $transaction->type === 'in'
                    ? $transaction->supplier
                    : ($transaction->recipient?->name ?? '-');

                foreach ($transaction->transactionDetails as $detail) {
                    fputcsv($file, [
                        $transaction->transaction_date->format('d/m/Y'),
                        $transaction->type === 'in' ? 'Masuk' : 'Keluar',
                        $party,
                        $transaction->division ?? '-',
                        $detail->item?->name ?? '-',
                        $detail->unit?->name ?? '-',
                        $detail->quantity,
                        $transaction->notes ?? '-'
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

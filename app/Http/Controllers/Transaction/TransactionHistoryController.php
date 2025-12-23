<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    public function toXlsx(Request $request)
    {
        $transactions = $this->getTransactionQueryBuilderByRequest($request)
            ->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // header
        $sheet->fromArray(source: [
            'ID',
            'Tipe',
            'Tanggal Transaksi',
            'Penerima/Pemasok',
            'Divisi',
            'Item',
            'Unit',
            'Jumlah',
            'Catatan',
        ], nullValue: null, startCell: 'A1');

        // data
        foreach ($transactions as $index => $transaction) {
            foreach ($transaction->transactionDetails as $detail) {
                $sheet->fromArray(source: [
                    $transaction->id,
                    $transaction->type == 'in' ? 'Masuk' : 'Keluar',
                    $transaction->transaction_date->format('Y-m-d'),
                    $transaction->recipient?->name ?? $transaction->supplier,
                    $transaction->recipient?->division ?? $transaction->division,
                    $detail->item->name,
                    $detail->unit->name,
                    $detail->quantity,
                    $transaction->notes,
                ], nullValue: null, startCell: 'A' . (2 + $index)); // mulai dari baris ke-2
            }
        }
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'history_trangsaksi_' . Date::now()->format('d_m_Y_i_s') . '.xlsx');
    }
}

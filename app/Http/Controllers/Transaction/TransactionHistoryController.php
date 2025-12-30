<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Inertia\Inertia;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
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
            'transactionItems.sku.item:id,name',
            'transactionItems.unit:id,name'
        ])->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier', 'like', "%{$search}%")
                    ->orWhere('division', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('recipient', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('division', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transactionItems.sku.item', function ($q) use ($search) {
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

        $callback = function () use ($transactions) {
            try {
                $writer = new Writer();
                $writer->openToFile("php://output");
                $writer->addRow(Row::fromValues([
                    'No',
                    'Tipe',
                    'Tanggal Transaksi',
                    'Penerima/Pemasok',
                    'Divisi',
                    'Barang',
                    'SKU',
                    'Satuan',
                    'Jumlah',
                    'Catatan',
                ]));

                $no = 0;
                foreach ($transactions as $index => $transaction) {
                    foreach ($transaction->transactionItems as $transactionItem) {
                        $writer->addRow(Row::fromValues([
                            ++$no,
                            __("common.transactions.$transaction->type"),
                            $transaction->transaction_date->format('Y-m-d'),
                            $transaction->recipient?->name ?? $transaction->supplier,
                            $transaction->recipient?->division ?? $transaction->division,
                            $transactionItem->sku->item->name,
                            $transactionItem->sku->sku,
                            $transactionItem->unit->name,
                            $transactionItem->quantity,
                            $transaction->notes,
                        ]));
                    }
                }
            } finally {
                $writer->close();
            }
        };

        return response()->streamDownload($callback, 'history_trangsaksi_' . Date::now()->format('d_m_Y_i_s') . '.xlsx');
    }
}

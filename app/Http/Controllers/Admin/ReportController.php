<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase; // Import the Purchase model
use App\Models\User; // Import the User model
use App\Models\Song; // Import the Song model
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Export sales report as CSV.
     */
    public function exportSales(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales_report_'.now()->format('Ymd_His').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, ['ID Pembelian', 'Nama Pengguna', 'Email Pengguna', 'Judul Lagu', 'Artis Lagu', 'Harga Pembelian', 'Tanggal Pembelian']);

            Purchase::with(['user', 'song'])->chunk(1000, function ($purchases) use ($file) {
                foreach ($purchases as $purchase) {
                    fputcsv($file, [
                        $purchase->id,
                        $purchase->user->name ?? 'N/A',
                        $purchase->user->email ?? 'N/A',
                        $purchase->song->title ?? 'N/A',
                        $purchase->song->artist ?? 'N/A',
                        $purchase->purchase_price,
                        $purchase->purchase_date->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}

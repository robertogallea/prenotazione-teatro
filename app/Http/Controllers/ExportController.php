<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function csv(): StreamedResponse
    {
        $filename = 'prenotazioni_'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($output, ['Settore', 'Blocco', 'Fila', 'Posto', 'Etichetta', 'Prenotato per', 'Note', 'Data prenotazione']);

            Booking::with('seat')->orderBy('created_at')->each(function (Booking $booking) use ($output): void {
                fputcsv($output, [
                    $booking->seat->section,
                    $booking->seat->block_key,
                    $booking->seat->row,
                    $booking->seat->number,
                    $booking->seat->label,
                    $booking->booked_for,
                    $booking->notes ?? '',
                    $booking->created_at->format('Y-m-d H:i:s'),
                ]);
            });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function pdf(): Response
    {
        $bookings = Booking::with('seat')->orderBy('created_at')->get();
        $filename = 'prenotazioni_'.now()->format('Y-m-d').'.pdf';

        return Pdf::loadView('exports.bookings_pdf', ['bookings' => $bookings])
            ->download($filename);
    }
}

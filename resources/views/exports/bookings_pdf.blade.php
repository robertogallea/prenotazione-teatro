<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Prenotazioni Teatro</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; font-size: 15px; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #666; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #185FA5; color: white; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .footer { margin-top: 16px; text-align: right; color: #9ca3af; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Elenco Prenotazioni Teatro</h1>
    <p class="subtitle">Totale prenotazioni: {{ $bookings->count() }}</p>
    <table>
        <thead>
            <tr>
                <th>Settore</th><th>Blocco</th><th>Fila</th><th>Posto</th>
                <th>Etichetta</th><th>Prenotato per</th><th>Note</th><th>Data</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ ucfirst($booking->seat->section) }}</td>
                <td>{{ $booking->seat->block_key }}</td>
                <td>{{ $booking->seat->row }}</td>
                <td>{{ $booking->seat->number }}</td>
                <td>{{ $booking->seat->label }}</td>
                <td>{{ $booking->booked_for }}</td>
                <td>{{ $booking->notes ?? '—' }}</td>
                <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Stampato il {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>

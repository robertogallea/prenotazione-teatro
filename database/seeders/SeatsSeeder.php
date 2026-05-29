<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * SeatsSeeder
 *
 * Popola le tabelle:
 *  - seats      (636 posti: 474 platea + 162 galleria)
 *  - seat_groups (meta-modello blocchi)
 *  - users       (amministratore unico)
 *
 * Meta-modello blocchi
 * ─────────────────────────────────────────────────────────────────────────
 * Ogni blocco è descritto da:
 *   section       → 'platea' | 'galleria'
 *   block_key     → chiave univoca (es. 'balconata', 'left', 'center', ...)
 *   label         → nome visualizzato
 *   sort_order    → ordine di rendering da sinistra a destra
 *   rows          → array JSON delle file (asse X, da sinistra a destra)
 *   seats_per_row → array JSON dei posti per fila (asse Y, dall'alto in basso)
 *                   i valori corrispondono ai numeri posto reali
 *   seat_offset   → offset verticale (in slot) rispetto agli altri blocchi
 *                   (usato per centrare blocchi con meno posti, es. balconata)
 *   has_corridor  → boolean: true se esiste un corridoio orizzontale interno
 *   corridor_after_slot → indice (0-based) dopo cui inserire il corridoio
 *
 * Questo meta-modello permette di rappresentare qualsiasi teatro:
 *   - blocchi con diverse numerazioni di fila/posto
 *   - blocchi centrati verticalmente (seat_offset > 0)
 *   - blocchi senza corridoio interno (balconata)
 *   - file con nomi alfanumerici ('F1g', 'A', 'B', ...)
 */
class SeatsSeeder extends Seeder
{
    // ── Struttura posti ────────────────────────────────────────────────────

    /** Posti platea: 9 pari + corridoio + 9 dispari (alto→basso = lontano→vicino palco) */
    const PLATEA_SEATS = [18, 16, 14, 12, 10, 8, 6, 4, 2, 17, 15, 13, 11, 9, 7, 5, 3, 1];

    /** Posti balconata: 12 consecutivi, numerazione propria */
    const BALC_SEATS = [12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

    /** Il corridoio nella platea cade dopo il 9° posto (dopo il posto 2) */
    const PLATEA_CORRIDOR_AFTER = 8; // indice 0-based (dopo slot 8 = dopo posto 2)

    // ── Blocchi ────────────────────────────────────────────────────────────

    const BLOCKS = [
        [
            'section' => 'platea',
            'block_key' => 'balconata',
            'label' => 'Balconata',
            'sort_order' => 1,
            'rows' => ['A', 'B', 'C', 'D', 'E'],
            'seats_per_row' => self::BALC_SEATS,
            'seat_offset' => 3,   // 3 slot vuoti sopra per centrare sui 18 della platea
            'has_corridor' => false,
            'corridor_after_slot' => null,
        ],
        [
            'section' => 'platea',
            'block_key' => 'left',
            'label' => 'Blocco sinistro',
            'sort_order' => 2,
            'rows' => [20, 21, 22],
            'seats_per_row' => self::PLATEA_SEATS,
            'seat_offset' => 0,
            'has_corridor' => true,
            'corridor_after_slot' => self::PLATEA_CORRIDOR_AFTER,
        ],
        [
            'section' => 'platea',
            'block_key' => 'center',
            'label' => 'Blocco centrale',
            'sort_order' => 3,
            'rows' => [10, 11, 12, 13, 14, 15, 16, 17, 18, 19],
            'seats_per_row' => self::PLATEA_SEATS,
            'seat_offset' => 0,
            'has_corridor' => true,
            'corridor_after_slot' => self::PLATEA_CORRIDOR_AFTER,
        ],
        [
            'section' => 'platea',
            'block_key' => 'right',
            'label' => 'Blocco destro',
            'sort_order' => 4,
            'rows' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            'seats_per_row' => self::PLATEA_SEATS,
            'seat_offset' => 0,
            'has_corridor' => true,
            'corridor_after_slot' => self::PLATEA_CORRIDOR_AFTER,
        ],
        [
            'section' => 'galleria',
            'block_key' => 'gallery',
            'label' => 'Galleria',
            'sort_order' => 1,
            'rows' => ['F1g', 25, 26, 27, 28, 29, 30, 31, 32],
            'seats_per_row' => self::PLATEA_SEATS,
            'seat_offset' => 0,
            'has_corridor' => true,
            'corridor_after_slot' => self::PLATEA_CORRIDOR_AFTER,
        ],
    ];

    // ── Run ────────────────────────────────────────────────────────────────

    public function run(): void
    {

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = replica');
        }
        DB::table('bookings')->truncate();
        DB::table('seats')->truncate();
        DB::table('seat_groups')->truncate();
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = DEFAULT');
        }

        $this->seedSeatGroups();
        $this->seedSeats();
        $this->seedAdmin();
    }

    // ── Seat groups ────────────────────────────────────────────────────────

    private function seedSeatGroups(): void
    {
        $now = now();
        foreach (self::BLOCKS as $block) {
            DB::table('seat_groups')->insert([
                'section' => $block['section'],
                'block_key' => $block['block_key'],
                'label' => $block['label'],
                'sort_order' => $block['sort_order'],
                'rows' => json_encode($block['rows']),
                'seats_per_row' => json_encode($block['seats_per_row']),
                'seat_offset' => $block['seat_offset'],
                'has_corridor' => $block['has_corridor'] ? 1 : 0,
                'corridor_after_slot' => $block['corridor_after_slot'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    // ── Seats ──────────────────────────────────────────────────────────────

    private function seedSeats(): void
    {
        $seats = [];
        $now = now();

        foreach (self::BLOCKS as $block) {
            foreach ($block['rows'] as $row) {
                foreach ($block['seats_per_row'] as $seatNum) {
                    $seats[] = [
                        'section' => $block['section'],
                        'block_key' => $block['block_key'],
                        'row' => (string) $row,
                        'number' => $seatNum,
                        'label' => $block['block_key'] === 'balconata'
                                            ? "{$row}{$seatNum}"
                                            : "F{$row}-P{$seatNum}",
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Insert in chunk per evitare limiti SQLite
        foreach (array_chunk($seats, 100) as $chunk) {
            DB::table('seats')->insert($chunk);
        }

        $this->command->info('Seats seeded: '.count($seats).' (atteso 636)');
    }

    // ── Admin ──────────────────────────────────────────────────────────────

    private function seedAdmin(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => env('ADMIN_EMAIL', 'admin@teatro.it')],
            [
                'name' => 'Amministratore',
                'email' => env('ADMIN_EMAIL', 'admin@teatro.it'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'changeme')),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

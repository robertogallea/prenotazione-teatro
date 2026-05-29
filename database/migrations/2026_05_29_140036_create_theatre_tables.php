<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrazione: seat_groups + seats + bookings
 *
 * seat_groups  → meta-modello blocchi (uno per blocco fisico del teatro)
 * seats        → ogni posto singolo (636 totali)
 * bookings     → prenotazioni (max una per posto)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── seat_groups ──────────────────────────────────────────────────
        Schema::create('seat_groups', function (Blueprint $table) {
            $table->id();
            $table->string('section');              // 'platea' | 'galleria'
            $table->string('block_key')->unique();  // 'balconata','left','center','right','gallery'
            $table->string('label');                // nome visualizzato
            $table->unsignedTinyInteger('sort_order'); // ordine rendering sx→dx
            $table->json('rows');                   // es. ["A","B","C","D","E"] o [20,21,22]
            $table->json('seats_per_row');          // es. [12,11,...,1] o [18,16,...,1]
            $table->unsignedTinyInteger('seat_offset')->default(0); // slot vuoti sopra per centratura
            $table->boolean('has_corridor')->default(true);
            $table->unsignedTinyInteger('corridor_after_slot')->nullable(); // indice 0-based
            $table->timestamps();
        });

        // ── seats ────────────────────────────────────────────────────────
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->string('section');              // 'platea' | 'galleria'
            $table->string('block_key');            // FK logica a seat_groups.block_key
            $table->string('row');                  // es. 'A', '10', 'F1g'
            $table->unsignedSmallInteger('number'); // numero posto reale
            $table->string('label');                // es. 'A12', 'F10-P18'
            $table->timestamps();

            $table->unique(['block_key', 'row', 'number']);
            $table->index(['section', 'block_key']);
        });

        // ── bookings ─────────────────────────────────────────────────────
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seat_id')
                ->unique()                        // un posto = una sola prenotazione
                ->constrained('seats')
                ->cascadeOnDelete();
            $table->string('booked_for');           // nome prenotante
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('seat_groups');
    }
};

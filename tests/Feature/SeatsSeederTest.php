<?php

use App\Models\Seat;
use App\Models\SeatGroup;
use Database\Seeders\SeatsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('seeder creates 636 seats total', function () {
    $this->seed(SeatsSeeder::class);
    expect(Seat::count())->toBe(636);
});

test('seeder creates 474 platea seats', function () {
    $this->seed(SeatsSeeder::class);
    expect(Seat::where('section', 'platea')->count())->toBe(474);
});

test('seeder creates 162 galleria seats', function () {
    $this->seed(SeatsSeeder::class);
    expect(Seat::where('section', 'galleria')->count())->toBe(162);
});

test('seeder creates 5 seat groups', function () {
    $this->seed(SeatsSeeder::class);
    expect(SeatGroup::count())->toBe(5);
});

test('seeder creates balconata group with correct config', function () {
    $this->seed(SeatsSeeder::class);
    $balconata = SeatGroup::where('block_key', 'balconata')->first();

    expect($balconata)->not->toBeNull();
    expect($balconata->rows)->toBe(['A', 'B', 'C', 'D', 'E']);
    expect($balconata->seat_offset)->toBe(3);
    expect($balconata->has_corridor)->toBeFalse();
});

test('seeder creates unique seat combinations per block', function () {
    $this->seed(SeatsSeeder::class);

    $duplicates = Seat::selectRaw('block_key, row, number, COUNT(*) as cnt')
        ->groupBy('block_key', 'row', 'number')
        ->havingRaw('COUNT(*) > 1')
        ->count();

    expect($duplicates)->toBe(0);
});

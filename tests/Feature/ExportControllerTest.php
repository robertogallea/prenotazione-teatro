<?php

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot access csv export', function () {
    $this->get('/export/csv')->assertRedirect('/login');
});

test('guest cannot access pdf export', function () {
    $this->get('/export/pdf')->assertRedirect('/login');
});

test('csv export returns streamed csv download', function () {
    $user = User::factory()->create();
    Booking::factory()->count(2)->create();

    $response = $this->actingAs($user)->get('/export/csv');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('prenotazioni_');
    expect($response->headers->get('content-disposition'))->toContain('.csv');
});

test('pdf export returns pdf download', function () {
    $user = User::factory()->create();
    Booking::factory()->count(2)->create();

    $response = $this->actingAs($user)->get('/export/pdf');

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('csv contains correct header columns', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/export/csv');
    $content = $response->streamedContent();

    expect($content)->toContain('Settore');
    expect($content)->toContain('Prenotato per');
    expect($content)->toContain('Data prenotazione');
});

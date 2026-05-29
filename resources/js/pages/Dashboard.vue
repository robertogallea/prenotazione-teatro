<script setup lang="ts">
import { computed, ref } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import type { Seat, SeatGroup } from '@/types';
import BookingController from '@/actions/App/Http/Controllers/BookingController';
import ExportController from '@/actions/App/Http/Controllers/ExportController';
import BookingModal from '@/components/BookingModal.vue';
import SeatMap from '@/components/SeatMap.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: dashboard() }],
    },
});

const props = defineProps<{
    seats: Seat[];
    seat_groups: SeatGroup[];
}>();

// ── Section tab ──────────────────────────────────────────────────────────
const activeSection = ref<'platea' | 'galleria'>('platea');

// ── Seat selection ───────────────────────────────────────────────────────
const selectedSeats = ref<Seat[]>([]);
const showModal = ref(false);
const inspectedSeat = ref<Seat | null>(null);

function toggleSeat(seat: Seat) {
    const idx = selectedSeats.value.findIndex((s) => s.id === seat.id);
    if (idx === -1) {
        selectedSeats.value.push(seat);
    } else {
        selectedSeats.value.splice(idx, 1);
    }
}

function openModal() {
    if (selectedSeats.value.length > 0) {
        showModal.value = true;
    }
}

function closeModal() {
    showModal.value = false;
    selectedSeats.value = [];
    inspectedSeat.value = null;
}

function switchSection(section: 'platea' | 'galleria') {
    activeSection.value = section;
    selectedSeats.value = [];
    inspectedSeat.value = null;
}

// ── Stats ────────────────────────────────────────────────────────────────
const freeCount = computed(() => props.seats.filter((s) => !s.booking).length);
const bookedCount = computed(() => props.seats.filter((s) => s.booking).length);

// ── Zoom ─────────────────────────────────────────────────────────────────
const zoom = ref(1);

function zoomIn() {
    zoom.value = Math.min(zoom.value + 0.25, 3);
}

function zoomOut() {
    zoom.value = Math.max(zoom.value - 0.25, 0.5);
}

function resetZoom() {
    zoom.value = 1;
}

function handleWheel(e: WheelEvent) {
    if (e.ctrlKey || e.metaKey) {
        e.preventDefault();
        zoom.value = Math.max(0.5, Math.min(3, zoom.value + (e.deltaY < 0 ? 0.1 : -0.1)));
    }
}
</script>

<template>
    <Head title="Mappa Teatro" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <!-- Header: stats + export buttons -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-4 text-sm">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm bg-green-600" />
                    Liberi: {{ freeCount }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm bg-red-500" />
                    Occupati: {{ bookedCount }}
                </span>
                <span v-if="selectedSeats.length > 0" class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm bg-yellow-400" />
                    Selezionati: {{ selectedSeats.length }}
                </span>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" size="sm" as="a" :href="ExportController.csv.url()">
                    Esporta CSV
                </Button>
                <Button variant="outline" size="sm" as="a" :href="ExportController.pdf.url()">
                    Esporta PDF
                </Button>
            </div>
        </div>

        <!-- Section tabs + zoom controls -->
        <div class="flex items-center justify-between">
            <div class="flex gap-2">
                <Button
                    :variant="activeSection === 'platea' ? 'default' : 'outline'"
                    size="sm"
                    @click="switchSection('platea')"
                >
                    Platea (474)
                </Button>
                <Button
                    :variant="activeSection === 'galleria' ? 'default' : 'outline'"
                    size="sm"
                    @click="switchSection('galleria')"
                >
                    Galleria (162)
                </Button>
            </div>
            <div class="flex items-center gap-1">
                <Button variant="ghost" size="sm" @click="zoomOut">−</Button>
                <button class="text-muted-foreground w-12 text-center text-xs" @click="resetZoom">
                    {{ Math.round(zoom * 100) }}%
                </button>
                <Button variant="ghost" size="sm" @click="zoomIn">+</Button>
            </div>
        </div>

        <!-- Seat map (scrollable container + zoomable inner div) -->
        <div class="overflow-auto rounded-xl border p-3" @wheel.passive="handleWheel">
            <div
                :style="{
                    transform: `scale(${zoom})`,
                    transformOrigin: 'top left',
                    display: 'inline-block',
                }"
            >
                <SeatMap
                    :seats="seats"
                    :seat-groups="seat_groups"
                    :selected-seats="selectedSeats"
                    :section="activeSection"
                    @select="toggleSeat"
                    @inspect="inspectedSeat = $event"
                />
            </div>
        </div>

        <!-- Inspect panel: shown when an occupied seat is clicked -->
        <div
            v-if="inspectedSeat?.booking"
            class="bg-card flex items-start justify-between gap-4 rounded-lg border p-4"
        >
            <div>
                <p class="font-medium">{{ inspectedSeat.label }}</p>
                <p class="text-muted-foreground text-sm">
                    Prenotato per:
                    <span class="text-foreground font-medium">{{ inspectedSeat.booking.booked_for }}</span>
                </p>
                <p v-if="inspectedSeat.booking.notes" class="text-muted-foreground text-sm">
                    {{ inspectedSeat.booking.notes }}
                </p>
            </div>
            <div class="flex shrink-0 gap-2">
                <Form
                    v-bind="BookingController.destroy.form({ booking: inspectedSeat.booking.id })"
                    class="inline"
                >
                    <Button type="submit" variant="destructive" size="sm">
                        Cancella prenotazione
                    </Button>
                </Form>
                <Button variant="ghost" size="sm" @click="inspectedSeat = null">Chiudi</Button>
            </div>
        </div>
    </div>

    <!-- Floating book button -->
    <div v-if="selectedSeats.length > 0" class="fixed bottom-6 right-6">
        <Button size="lg" class="shadow-lg" @click="openModal">
            Prenota {{ selectedSeats.length }} posto/i
        </Button>
    </div>

    <BookingModal :selected-seats="selectedSeats" :show="showModal" @close="closeModal" />
</template>

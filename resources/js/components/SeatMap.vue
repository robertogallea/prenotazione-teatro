<script setup lang="ts">
import { computed } from 'vue';
import type { Seat, SeatGroup } from '@/types';
import SeatCell from '@/components/SeatCell.vue';

const props = defineProps<{
    seats: Seat[];
    seatGroups: SeatGroup[];
    selectedSeats: Seat[];
    section: 'platea' | 'galleria';
}>();

const emit = defineEmits<{
    select: [seat: Seat];
    inspect: [seat: Seat];
}>();

const TOTAL_SLOTS = 19;
const SEAT_H = 13;
const SEAT_M = 1.5;
const GAP_H = 8;

const stageHeight = computed(
    () => TOTAL_SLOTS * (SEAT_H + SEAT_M * 2) - (SEAT_H + SEAT_M * 2 - GAP_H),
);

const seatLookup = computed(() => {
    const map = new Map<string, Seat>();
    for (const seat of props.seats) {
        map.set(`${seat.block_key}:${seat.row}:${seat.number}`, seat);
    }
    return map;
});

const filteredBlocks = computed(() =>
    props.seatGroups
        .filter((g) => g.section === props.section)
        .sort((a, b) => a.sort_order - b.sort_order),
);

type Slot =
    | { type: 'seat'; seat: Seat | null; number: number }
    | { type: 'empty' }
    | { type: 'gap' };

function buildColumnSlots(block: SeatGroup, row: string | number): Slot[] {
    const slots: Slot[] = [];
    const half = block.corridor_after_slot !== null
        ? block.corridor_after_slot + 1
        : Math.ceil(block.seats_per_row.length / 2);

    for (let i = 0; i < block.seat_offset; i++) {
        slots.push({ type: 'empty' });
    }

    for (let i = 0; i < half; i++) {
        const num = block.seats_per_row[i];
        const seat = seatLookup.value.get(`${block.block_key}:${row}:${num}`) ?? null;
        slots.push({ type: 'seat', seat, number: num });
    }

    slots.push({ type: 'gap' });

    for (let i = half; i < block.seats_per_row.length; i++) {
        const num = block.seats_per_row[i];
        const seat = seatLookup.value.get(`${block.block_key}:${row}:${num}`) ?? null;
        slots.push({ type: 'seat', seat, number: num });
    }

    while (slots.length < TOTAL_SLOTS) {
        slots.push({ type: 'empty' });
    }

    return slots;
}

type Label = { type: 'value'; text: string | number } | { type: 'empty' } | { type: 'gap' };

function buildLabels(block: SeatGroup): Label[] {
    const labels: Label[] = [];
    const half = block.corridor_after_slot !== null
        ? block.corridor_after_slot + 1
        : Math.ceil(block.seats_per_row.length / 2);

    for (let i = 0; i < block.seat_offset; i++) {
        labels.push({ type: 'empty' });
    }
    for (let i = 0; i < half; i++) {
        labels.push({ type: 'value', text: block.seats_per_row[i] });
    }
    labels.push({ type: 'gap' });
    for (let i = half; i < block.seats_per_row.length; i++) {
        labels.push({ type: 'value', text: block.seats_per_row[i] });
    }
    while (labels.length < TOTAL_SLOTS) {
        labels.push({ type: 'empty' });
    }

    return labels;
}

function shouldShowLabels(block: SeatGroup, index: number): boolean {
    if (index === 0) {
        return true;
    }
    const prev = filteredBlocks.value[index - 1];
    return JSON.stringify(block.seats_per_row) !== JSON.stringify(prev.seats_per_row);
}

function isSelected(seat: Seat): boolean {
    return props.selectedSeats.some((s) => s.id === seat.id);
}
</script>

<template>
    <div class="teatro flex items-start">
        <template v-for="(block, blockIndex) in filteredBlocks" :key="block.block_key">
            <!-- Y-axis labels (shown when seats_per_row changes from previous block) -->
            <template v-if="shouldShowLabels(block, blockIndex)">
                <div class="rl-col flex flex-col" style="padding-top: 18px">
                    <template v-for="(lbl, li) in buildLabels(block)" :key="li">
                        <div
                            v-if="lbl.type === 'gap'"
                            :style="{ height: GAP_H + 'px', margin: '0 1.5px' }"
                        />
                        <div
                            v-else
                            class="box-border text-right text-[9px] text-muted-foreground"
                            style="width: 16px; margin: 1.5px 0; line-height: 13px; height: 13px"
                        >
                            {{ lbl.type === 'value' ? lbl.text : '' }}
                        </div>
                    </template>
                </div>
                <div style="width: 3px; flex-shrink: 0" />
            </template>

            <!-- Columns: one per row identifier in this block -->
            <div
                v-for="row in block.rows"
                :key="String(row)"
                class="col flex flex-col items-center"
            >
                <!-- Column header: file identifier (e.g. 'A', '10', 'F1g') -->
                <div
                    class="flex items-end justify-center text-[8px] font-medium text-blue-600"
                    style="height: 18px; width: 16px"
                >
                    {{ row }}
                </div>

                <!-- Seat slots for this column -->
                <template v-for="(slot, si) in buildColumnSlots(block, row)" :key="si">
                    <div
                        v-if="slot.type === 'gap'"
                        style="height: 8px; margin: 0 1.5px; width: 13px; flex-shrink: 0"
                    />
                    <div
                        v-else-if="slot.type === 'empty'"
                        style="height: 13px; width: 13px; margin: 1.5px; flex-shrink: 0"
                    />
                    <SeatCell
                        v-else-if="slot.seat"
                        :seat="slot.seat"
                        :is-selected="isSelected(slot.seat)"
                        @select="emit('select', $event)"
                        @inspect="emit('inspect', $event)"
                    />
                    <!-- Seat in layout but not in DB (shouldn't happen after seeding) -->
                    <div
                        v-else
                        style="
                            height: 13px;
                            width: 13px;
                            margin: 1.5px;
                            flex-shrink: 0;
                            border-radius: 2px;
                            background: #e5e7eb;
                        "
                    />
                </template>
            </div>

            <!-- Vertical corridor between blocks (not after the last block) -->
            <div
                v-if="blockIndex < filteredBlocks.length - 1"
                style="width: 16px; flex-shrink: 0"
            />
        </template>

        <!-- Stage (right side, aligned with seat rows) -->
        <div class="flex items-start" style="margin-left: 6px; padding-top: 18px">
            <div
                class="flex items-center justify-center rounded text-[10px] tracking-widest text-muted-foreground"
                style="
                    writing-mode: vertical-rl;
                    width: 24px;
                    border: 0.5px solid var(--color-border, #e5e7eb);
                    background: var(--color-muted, #f9fafb);
                "
                :style="{ height: stageHeight + 'px' }"
            >
                PALCOSCENICO
            </div>
        </div>
    </div>
</template>

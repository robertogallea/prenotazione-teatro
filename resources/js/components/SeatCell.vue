<script setup lang="ts">
import { computed } from 'vue';
import type { Seat } from '@/types';

const props = defineProps<{
    seat: Seat;
    isSelected: boolean;
}>();

const emit = defineEmits<{
    select: [seat: Seat];
    inspect: [seat: Seat];
}>();

const cellClass = computed(() => {
    if (props.isSelected) return 'bg-yellow-400 hover:opacity-80';
    if (props.seat.booking) return 'bg-red-500 hover:opacity-80';
    return 'bg-green-600 hover:opacity-80';
});

function handleClick() {
    if (props.seat.booking) {
        emit('inspect', props.seat);
    } else {
        emit('select', props.seat);
    }
}
</script>

<template>
    <button
        :title="seat.booking ? `${seat.label} — ${seat.booking.booked_for}` : seat.label"
        :class="[
            'box-border flex h-[13px] w-[13px] shrink-0 cursor-pointer items-center justify-center rounded-[2px] transition-opacity',
            cellClass,
        ]"
        style="margin: 1.5px"
        type="button"
        @click="handleClick"
    />
</template>

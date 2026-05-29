<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Seat } from '@/types';
import BookingController from '@/actions/App/Http/Controllers/BookingController';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    selectedSeats: Seat[];
    show: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({
    seat_ids: [] as number[],
    booked_for: '',
    notes: '',
});

watch(
    () => props.show,
    (open) => {
        if (open) {
            form.reset();
            form.seat_ids = props.selectedSeats.map((s) => s.id);
        }
    },
);

function submit() {
    form.seat_ids = props.selectedSeats.map((s) => s.id);
    form.post(BookingController.store.url(), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
}
</script>

<template>
    <Dialog :open="show" @update:open="emit('close')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Prenota {{ selectedSeats.length }} posto/i</DialogTitle>
            </DialogHeader>

            <div class="space-y-4 py-2">
                <p class="text-muted-foreground text-sm">
                    Posti:
                    <span class="text-foreground font-medium">
                        {{ selectedSeats.map((s) => s.label).join(', ') }}
                    </span>
                </p>

                <div class="grid gap-1.5">
                    <Label for="booked_for">Nome prenotante *</Label>
                    <Input
                        id="booked_for"
                        v-model="form.booked_for"
                        placeholder="Nome e Cognome"
                        required
                    />
                    <InputError :message="form.errors.booked_for" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="notes">Note (opzionale)</Label>
                    <Input id="notes" v-model="form.notes" placeholder="Note aggiuntive" />
                </div>

                <InputError :message="form.errors.seat_ids" />
            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('close')">Annulla</Button>
                <Button
                    :disabled="form.processing || !form.booked_for.trim()"
                    @click="submit"
                >
                    Conferma prenotazione
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

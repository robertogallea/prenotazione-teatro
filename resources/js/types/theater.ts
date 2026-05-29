export interface Booking {
    id: number;
    seat_id: number;
    booked_for: string;
    notes: string | null;
    created_at: string;
    updated_at: string;
}

export interface Seat {
    id: number;
    section: 'platea' | 'galleria';
    block_key: string;
    row: string;
    number: number;
    label: string;
    booking: Booking | null;
    created_at: string;
    updated_at: string;
}

export interface SeatGroup {
    id: number;
    section: 'platea' | 'galleria';
    block_key: string;
    label: string;
    sort_order: number;
    rows: (string | number)[];
    seats_per_row: number[];
    seat_offset: number;
    has_corridor: boolean;
    corridor_after_slot: number | null;
}

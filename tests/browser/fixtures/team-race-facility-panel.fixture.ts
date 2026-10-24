export interface FacilityRow {
    label: string;
    value: string | number | null;
    state: string | null;
    absence: string | null;
}

export interface FacilityGroup {
    group: string;
    rows: FacilityRow[];
    empty_note: string | null;
}

export interface FacilityScenario {
    facilities: FacilityGroup[];
    alerts: { level: string; text: string; detail: string | null }[];
}

export const mockScenario: FacilityScenario = {
    facilities: [
        {
            group: 'Track Facility',
            rows: [
                {
                    label: 'Turf Course',
                    value: 'Level 3',
                    state: 'active',
                    absence: null,
                },
                {
                    label: 'Dirt Course',
                    value: 'Level 2',
                    state: 'completed',
                    absence: null,
                },
            ],
            empty_note: null,
        },
        {
            group: 'Training Facility',
            rows: [
                {
                    label: 'Weight Room',
                    value: 'Level 1',
                    state: 'warning',
                    absence: null,
                },
                {
                    label: 'Pool',
                    value: null,
                    state: null,
                    absence: 'Facility closed for maintenance',
                },
            ],
            empty_note: null,
        },
    ],
    alerts: [
        {
            level: 'Notice',
            text: 'Special training bonuses available.',
            detail: null,
        },
    ],
};

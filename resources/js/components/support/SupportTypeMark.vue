<script setup lang="ts">
/*
 * The seven support types as the client prints them: a coloured rounded tile carrying a white glyph.
 *
 * The tile-and-glyph badge is the client's own type-chip anatomy, and the colours are the client's
 * chip hues (blue shoe, red heart, orange arm, pink flame, green cap, amber Pal, green Group). All
 * seven are the icon set authored under `docs/design-research/icons_svg/`; the tile is filled from a
 * per-type design token and the glyph is white on top, so the seven read apart at a glance and stay
 * legible against the dark raised surface (DESIGN-CORPUS.md §6.0b, UMAMUSUME_REFERENCE.md §1.4.7).
 *
 * The word is the label; the badge is beside it and never replaces it. `design-2.0` §17 forbids
 * communicating a value through colour alone, and a chip whose only signal is a coloured tile is
 * unreadable to a screen reader and to a Trainer who cannot separate the seven fills. So the badge is
 * `aria-hidden`, the word carries the meaning, and the two are one focus-free unit a reader takes in
 * whole.
 *
 * One collision the source itself records: the heart is Stamina's type chip here and the client's HUD
 * uses a heart for Energy elsewhere, which is why one glyph carries one meaning here and never doubles.
 */
import { computed, useId } from 'vue';

const props = defineProps<{
    /** The export's key, one of `SupportCard::TYPES`. */
    type: string;
    /** The client's word for it: Speed, Stamina, Power, Guts, Wit, Pal, Group. */
    label: string;
}>();

/** One drawing primitive of a glyph. Flat and typed so the template renders it without `v-html`. */
type Prim =
    | { kind: 'path'; d: string; fill?: string; stroke?: string; width?: number; cap?: string; join?: string; opacity?: number; paintOrder?: string }
    | { kind: 'circle'; cx: number; cy: number; r: number; fill?: string; stroke?: string; width?: number; opacity?: number }
    | { kind: 'rect'; x: number; y: number; w: number; h: number; rx: number; fill?: string; stroke?: string; width?: number; opacity?: number };

interface Mark {
    /** The design token that fills the tile; the gradient's lower stop is mixed from it in CSS. */
    tile: string;
    /** An optional rotation applied to the whole glyph, for the shoe's tilt. */
    transform?: string;
    prims: Prim[];
}

/*
 * The central type-to-icon mapping. One row per `SupportCard::TYPES` key. Pages never carry SVG
 * markup; they pass a `type` key and this table resolves both the tile and the glyph, so a new surface
 * is one component reuse rather than a copied `<svg>`.
 */
const MARKS: Record<string, Mark> = {
    // Speed: a running shoe in side profile, tilted, over a sole.
    speed: {
        tile: 'var(--color-type-speed)',
        transform: 'rotate(18 50 52)',
        prims: [
            { kind: 'rect', x: 37, y: 20, w: 38, h: 10, rx: 1.5 },
            { kind: 'path', d: 'M39 34 H73 V71 H23 C19.5 71 18.5 67.5 20.5 64.5 C23 61 27.5 58.5 33 57.5 L39 56.5 Z' },
            { kind: 'rect', x: 21, y: 75, w: 52, h: 8, rx: 1.5 },
        ],
    },
    // Stamina: a heart.
    stamina: {
        tile: 'var(--color-type-stamina)',
        prims: [
            { kind: 'path', d: 'M50 79 C45 74.5 18 58 18 39.5 C18 30 25.5 23.5 34.5 23.5 C41.5 23.5 47 27.5 50 33 C53 27.5 58.5 23.5 65.5 23.5 C74.5 23.5 82 30 82 39.5 C82 58 55 74.5 50 79 Z' },
        ],
    },
    // Power: a flexed arm.
    power: {
        tile: 'var(--color-type-power)',
        prims: [
            { kind: 'path', d: 'M20 86 L33 40 C34.5 32 38 25.5 46 23.5 L56.5 20.5 C61 19.5 63.5 22.5 62.5 26.5 L60.5 34.5 C59.5 37.5 56.5 39 53 39 L48.5 39.5 L44 58 C47.5 55.5 51 54 55 53.5 C61.5 52.5 67.5 53 73 56 C80.5 60 84.5 67 84.5 75 L84.5 86 Z' },
            { kind: 'path', d: 'M58 57 C55.5 61 56 67 61 71', fill: 'none', stroke: 'color-mix(in srgb, var(--color-type-power) 78%, black)', width: 2.5, cap: 'round' },
        ],
    },
    // Guts: a flame, with its inner core.
    guts: {
        tile: 'var(--color-type-guts)',
        prims: [
            { kind: 'path', d: 'M50 16 C52 29 71 38 73 58 C74.5 73 63.5 85 50 85 C36.5 85 26 74.5 27 60 C27.8 50 34.5 43.5 39.5 37 C40.5 45 43.5 49.5 47 52 C45.5 40 47.5 27 50 16 Z' },
            { kind: 'path', d: 'M50 85 C44 83 41.5 77 42.5 70.5 C44.5 72.5 46.5 73.5 48 74.5 C48 68.5 50.5 63.5 54.5 59.5 C55.5 66 61 70.5 59 78.5 C58 82.5 54.5 85 50 85 Z', fill: 'color-mix(in srgb, var(--color-type-guts) 72%, black)' },
        ],
    },
    // Wit (the client's word for `intelligence`): a mortarboard.
    intelligence: {
        tile: 'var(--color-type-wit)',
        prims: [
            { kind: 'path', d: 'M26 47 L26 70 C38 77 56 77 66 71 L66 51 L48 57 Z' },
            { kind: 'path', d: 'M13 38 L52 25 L88 37 L49 51 Z', stroke: 'color-mix(in srgb, var(--color-type-wit) 80%, black)', width: 3, join: 'round' },
            { kind: 'path', d: 'M50 38 L77 43 L77 62', fill: 'none', stroke: 'var(--color-mark-ink)', width: 3, cap: 'round' },
            { kind: 'rect', x: 72, y: 61, w: 10, h: 18, rx: 3.5 },
        ],
    },
    // Pal (the export's `friend`): a smiling figure on an amber tile.
    friend: {
        tile: 'var(--color-type-friend)',
        prims: [
            { kind: 'path', d: 'M24 86 C24 70 35 62 50 62 C65 62 76 70 76 86 Z' },
            { kind: 'circle', cx: 50, cy: 38, r: 20 },
            { kind: 'circle', cx: 43, cy: 35, r: 2.8, fill: 'var(--color-mark-pal)' },
            { kind: 'circle', cx: 57, cy: 35, r: 2.8, fill: 'var(--color-mark-pal)' },
            { kind: 'path', d: 'M41 44 Q50 52 59 44', fill: 'none', stroke: 'var(--color-mark-pal)', width: 3.2, cap: 'round' },
        ],
    },
    // Group: a front figure over two faded rear figures, on a green tile.
    group: {
        tile: 'var(--color-type-group)',
        prims: [
            { kind: 'circle', cx: 23, cy: 43, r: 11, fill: 'var(--color-mark-group-soft)', opacity: 0.85 },
            { kind: 'path', d: 'M7 82 C7 67 14 59 23 59 C30 59 35 63 37 70 L37 82 Z', fill: 'var(--color-mark-group-soft)', opacity: 0.85 },
            { kind: 'circle', cx: 77, cy: 43, r: 11, fill: 'var(--color-mark-group-soft)', opacity: 0.85 },
            { kind: 'path', d: 'M93 82 C93 67 86 59 77 59 C70 59 65 63 63 70 L63 82 Z', fill: 'var(--color-mark-group-soft)', opacity: 0.85 },
            { kind: 'path', d: 'M27 86 C27 71 37 63 50 63 C63 63 73 71 73 86 Z', stroke: 'var(--color-mark-group-line)', width: 3, paintOrder: 'stroke' },
            { kind: 'circle', cx: 50, cy: 40, r: 18, stroke: 'var(--color-mark-group-line)', width: 3, paintOrder: 'stroke' },
            { kind: 'circle', cx: 43.5, cy: 37, r: 2.6, fill: 'var(--color-mark-group-line)' },
            { kind: 'circle', cx: 56.5, cy: 37, r: 2.6, fill: 'var(--color-mark-group-line)' },
            { kind: 'path', d: 'M42 45 Q50 52 58 45', fill: 'none', stroke: 'var(--color-mark-group-line)', width: 3, cap: 'round' },
        ],
    },
};

const mark = computed(() => MARKS[props.type] ?? null);

/*
 * A per-instance suffix for the gradient id. Two marks on one page would otherwise share `#type-bg-…`
 * and every tile would resolve to the first gradient defined in the document.
 */
const uid = useId();
</script>

<template>
    <span class="inline-flex items-center gap-1 text-xs text-ink-strong">
        <svg
            v-if="mark"
            viewBox="0 0 100 100"
            class="size-[18px] shrink-0"
            aria-hidden="true"
        >
            <defs>
                <linearGradient :id="`type-bg-${type}-${uid}`" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" :stop-color="mark.tile" />
                    <stop offset="1" :stop-color="`color-mix(in srgb, ${mark.tile} 82%, black)`" />
                </linearGradient>
            </defs>
            <rect x="2" y="2" width="96" height="96" rx="22" :fill="`url(#type-bg-${type}-${uid})`" />
            <g :transform="mark.transform" :fill="'var(--color-mark-ink)'">
                <template v-for="(prim, index) in mark.prims" :key="index">
                    <rect
                        v-if="prim.kind === 'rect'"
                        :x="prim.x"
                        :y="prim.y"
                        :width="prim.w"
                        :height="prim.h"
                        :rx="prim.rx"
                        :fill="prim.fill ?? 'var(--color-mark-ink)'"
                        :stroke="prim.stroke"
                        :stroke-width="prim.width"
                        :opacity="prim.opacity"
                    />
                    <circle
                        v-else-if="prim.kind === 'circle'"
                        :cx="prim.cx"
                        :cy="prim.cy"
                        :r="prim.r"
                        :fill="prim.fill ?? 'var(--color-mark-ink)'"
                        :stroke="prim.stroke"
                        :stroke-width="prim.width"
                        :opacity="prim.opacity"
                        :paint-order="prim.paintOrder"
                    />
                    <path
                        v-else
                        :d="prim.d"
                        :fill="prim.fill ?? 'var(--color-mark-ink)'"
                        :stroke="prim.stroke"
                        :stroke-width="prim.width"
                        :stroke-linecap="prim.cap"
                        :stroke-linejoin="prim.join"
                        :opacity="prim.opacity"
                        :paint-order="prim.paintOrder"
                    />
                </template>
            </g>
        </svg>
        <span>{{ label }}</span>
    </span>
</template>

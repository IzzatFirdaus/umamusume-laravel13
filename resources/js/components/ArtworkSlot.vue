<script setup lang="ts">
/*
 * The read half of `ADR-0021` for the ported pages, and the single owner of the slot contract.
 *
 * Three states are three separate props rather than one flag, and that is why this component
 * exists. The two Blade components it replaces took a single `decorative` flag that blanked the
 * image `alt` and the anchor `aria-label` together, which could not express any of the three
 * combinations the specs call for:
 *
 *   - `design-2.0` §42 wants a decorative image (`alt=""`) wherever the host already prints the
 *     name beside the slot, so a screen reader does not read the name twice.
 *   - §42 separately wants a clickable slot's accessible name to be that printed name verbatim
 *     (WCAG 2.5.3 label in name). A decorative image and a named link are different defects, and
 *     blanking one must not blank the other.
 *   - §45a gives some slots the click action "no action" outright, and those must render no
 *     anchor at all. The Blade components always emitted one, which is how a detail page ended up
 *     carrying a link to the page it was already on.
 *
 * So `alt` decides the image and `href` + `linkLabel` decide the link, each chosen on its own.
 *
 * The rules this owns, all from `DESIGN.md` §4.7:
 *
 *   - **Absence is a normal state, never an error state.** A null `url` renders nothing at all:
 *     no frame, no grey box, no loader, no placeholder glyph, and no anchor stranded around an
 *     absent image. This is why the guard wraps both branches rather than the `<img>` inside one.
 *   - **`src` is local.** The url is the loopback `artwork.show` route, resolved server-side by
 *     `ArtworkMirror::url()`, never the asset host: §7 forbids a CDN and promises the catalog
 *     works with zero network, so a third-party host in rendered HTML would make the page depend
 *     on someone else's uptime.
 *   - **Geometry is the caller's `size`, recorded once.** No `width`/`height` attributes: the
 *     Tailwind box sets both edges, so the square is reserved before the file arrives and there is
 *     no layout shift for intrinsic dimensions to paper over.
 *   - **Alt is the client display name and nothing else.** C-4 bounds its vocabulary, and the tool
 *     cannot see inside the file it would be describing, so there is no invented descriptor.
 *
 * The `size` union is typed rather than `string` on purpose. Tailwind's scanner reads raw source
 * text, so pinning the three values `DESIGN.md` §4.7 records here guarantees the classes are
 * generated even though the binding is dynamic.
 */
defineProps<{
    /** `ArtworkMirror::url()`, or null when the mirror holds no file. This is the absence guard. */
    url: string | null;
    /**
     * The image alt. The client display name, or the empty string wherever the host already
     * prints the name beside the slot.
     */
    alt: string;
    /** The recorded geometry. `DESIGN.md` §4.7 owns the values; the caller only picks one. */
    size: 'size-10' | 'size-12' | 'size-16';
    /** `§45a`'s click action. Null or omitted is the "no action" case, and renders no anchor. */
    href?: string | null;
    /**
     * The anchor's accessible name, required only when the image is decorative: a non-empty `alt`
     * already names the link it sits inside. The printed row name verbatim (`§42`, WCAG 2.5.3).
     */
    linkLabel?: string | null;
}>();
</script>

<template>
    <a
        v-if="url && href"
        :href="href"
        :aria-label="alt === '' ? (linkLabel ?? undefined) : undefined"
        :class="[size, 'block']"
    >
        <img :src="url" :alt="alt" :class="`${size} rounded-md object-cover`">
    </a>
    <img
        v-else-if="url"
        :src="url"
        :alt="alt"
        :class="`${size} rounded-md object-cover`"
    >
</template>

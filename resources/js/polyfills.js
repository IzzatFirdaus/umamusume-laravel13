/**
 * Feature-detected polyfills for non-Baseline tooltip platform features.
 *
 * Never loaded unconditionally — each import is gated on a runtime probe so
 * browsers with native support skip the polyfill entirely (MWG:
 * interest-triggered-tooltips / popover guides).
 *
 * - @oddbird/popover-polyfill: polyfills the `popover` attribute and
 *   showPopover/hidePopover/togglePopover. Does NOT polyfill `popover="hint"`
 *   behavior; where hint is unsupported the value defaults to `manual`.
 * - interestfor: polyfills the `interestfor` attribute so hint popovers open on
 *   hover/focus in Firefox and Safari.
 */
if (!("popover" in HTMLElement.prototype)) {
    import("@oddbird/popover-polyfill");
}

if (!("interestForElement" in HTMLButtonElement.prototype)) {
    import("interestfor");
}

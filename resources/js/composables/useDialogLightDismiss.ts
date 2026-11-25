import { onBeforeUnmount, onMounted, type Ref } from 'vue';

/**
 * MWG F9 fallback for `closedby="any"`.
 *
 * `closedby` is Limited availability (Chrome 134+, Firefox 141+; not Safari), so a modal `<dialog>`
 * carrying only that attribute behaves as `"closerequest"` where it is absent: Escape still closes
 * it, but a backdrop click does nothing. The guidance marks the fallback **mandatory** and prescribes
 * this pointer-position check, gated on `'closedBy' in HTMLDialogElement.prototype`.
 *
 * No-op where `closedby` is native, so the two paths never both act on one click. Call from the
 * component that owns the dialog ref; the listener is bound once on mount and removed on unmount.
 */
export function useDialogLightDismiss(dialog: Ref<HTMLDialogElement | null>): void {
    if ('closedBy' in HTMLDialogElement.prototype) {
        return;
    }

    function onBackdropClick(event: MouseEvent): void {
        const el = dialog.value;

        if (el === null) {
            return;
        }

        const rect = el.getBoundingClientRect();
        const insideDialog =
            rect.top <= event.clientY
            && event.clientY <= rect.bottom
            && rect.left <= event.clientX
            && event.clientX <= rect.right;

        // A click whose point falls outside the dialog's own box landed on the backdrop, which is
        // exactly what `closedby="any"` would have treated as light dismiss.
        if (!insideDialog) {
            el.close();
        }
    }

    onMounted(() => dialog.value?.addEventListener('click', onBackdropClick));
    onBeforeUnmount(() => dialog.value?.removeEventListener('click', onBackdropClick));
}

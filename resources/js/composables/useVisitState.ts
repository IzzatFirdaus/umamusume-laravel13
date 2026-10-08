import { onUnmounted, ref, type Ref } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * The one owner of the page-level visit lifecycle: "a visit is in flight" and "the last one failed",
 * the two states `ADR-0007` asks a page to announce rather than draw as a skeleton.
 *
 * **A prefetch is not a visit.** Inertia fires the same `start` event for a `Link`'s hover prefetch as
 * for a real navigation (`Request::send()` fires it before it looks at `prefetch`), so a handler that
 * sets the loading flag on every `start` lights up the page the Trainer is already on every time the
 * pointer crosses the sidebar. It is worse than a flicker: a prefetch superseded by the next one is
 * cancelled, and a cancelled request never fires `finish` (`Request::finish()` returns early once
 * `cancelled` or `interrupted` is set), so nothing clears the flag and the page announces a load that
 * already ended. The `prefetch` guard below is the whole reason this lives in one place instead of
 * being copied per page.
 *
 * `exception` and `invalid` return `false` so Inertia suppresses its own error modal and the page's
 * single `role="alert"` stays the only surface (`Cockpit.vue`'s note).
 */
export function useVisitState(): { visiting: Ref<boolean>; visitFailed: Ref<boolean> } {
    const visiting = ref(false);
    const visitFailed = ref(false);

    const stopStart = router.on('start', (event) => {
        if (event.detail.visit.prefetch) {
            return;
        }

        visiting.value = true;
        visitFailed.value = false;
    });
    const stopFinish = router.on('finish', () => {
        visiting.value = false;
    });
    const stopException = router.on('exception', () => {
        visiting.value = false;
        visitFailed.value = true;

        return false;
    });
    const stopInvalid = router.on('invalid', () => {
        visiting.value = false;
        visitFailed.value = true;

        return false;
    });

    onUnmounted(() => {
        stopStart();
        stopFinish();
        stopException();
        stopInvalid();
    });

    return { visiting, visitFailed };
}

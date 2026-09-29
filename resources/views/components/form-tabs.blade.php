@props([
    // The trainee's forms, already ordered by the controller's card scope. The component does
    // not re-sort: the list page and the detail page must agree on what "first form" means, and
    // two sort orders in two place is how they stop agreeing.
    'cards',
    // The open form, or null when the trainee has none. Resolved by the controller from `?form=`
    // against the same scoped collection, so it can never name a form this page is hiding.
    'active' => null,
    // Where the GET posts to.
    'action' => null,
    'showUnconfirmed' => false,
    // Passed straight into the panel include, because a form's body is about this trainee as
    // much as about the form: the aptitude letters live on her, not on the card.
    'trainee',
    // The partial that renders one form's body. A prop rather than a slot because a Blade
    // component has exactly one `$slot` and this needs N of them, one per form.
    'panelView',
])

{{--
    The multi-form control: a tab strip, so a trainee with three costumes is one page with a
    choice rather than a vertical stack of near-identical screens.

    **Native radios, CSS `:checked`, no script, and that combination is the point.**
    `components/guided-step.blade.php` is the precedent for a radio group wearing a client's
    banner shape, and it records why the element is a real `<input type="radio">` rather than a
    `role="radio"` div: native radios rove focus with the arrow keys for free, which is the half
    of G-11 a script would otherwise have to reimplement badly. The panel swap is `:checked ~`,
    so there is no per-frame work and nothing animates unless the Trainer caused it (D-90, D-91,
    D-92).

    **The one thing a CSS-only tab strip cannot be is addressable, so this is a GET form.**
    Such a strip never touches the URL, which would mean a link into the second form could not be
    shared and would not survive a reload. The radios therefore sit in a GET form with one
    submit control: selecting a tab switches the visible panel immediately through CSS, and the
    submit writes `?form={local card id}` so the choice is a URL a Trainer can copy and the back
    button still works. This is the WAI-ARIA *manual activation* shape — arrow keys move, the
    button commits — and it is a real trade: with no script there is no way to have both instant
    panel switching and an address that updates on the same keystroke.

    **Each radio gets a named peer** (`peer/t0`, `peer/t1`, …) rather than sharing one `peer`
    class. A shared `peer` would make `.peer:checked ~ .peer-checked\:block` match *every* panel,
    because the general sibling combinator does not care which radio was checked — all the
    panels would open at once. One name per radio is what keeps one panel open.

    **`sr-only` on the radio, not the guided rail's `size-px opacity-0`.** The rail's comment
    explains its choice: a zero-size box reads as "not visible" to tools that measure visibility,
    which made the rail untestable at the control that carries its state. That matters when
    something clicks the input directly. Here the label is the click target and the input is
    reached by Tab, so the trade runs the other way: `sr-only` clips the input out of flow, where
    a `size-px` radio sitting inline before each tab label would leave a 1px box of space between
    tabs. The focus ring is on the label either way, via `peer-focus-visible`.

    **Keyboard contract (G-11):** one Tab stop reaches the group, arrow keys move between tabs,
    Enter or the button commits. Nothing here is a div with a click handler.
--}}
@if ($cards->isNotEmpty())
    <form method="GET" action="{{ $action }}" class="mt-8">
        @if ($showUnconfirmed)
            {{-- The disclosure must survive a tab change, or activating a tab would quietly
                 close it and hide the very forms the Trainer asked to see. --}}
            <input type="hidden" name="show_unconfirmed" value="1">
        @endif

        @foreach ($cards as $index => $card)
            @php $peer = 't'.$index; @endphp
            <input type="radio" name="form" id="form-tab-{{ $card->id }}" value="{{ $card->id }}"
                   class="peer/{{ $peer }} sr-only"
                   aria-label="{{ $card->title }}"
                   @checked($active !== null && $card->id === $active->id)>
            <label for="form-tab-{{ $card->id }}"
                   class="{{ $active !== null && $card->id === $active->id
                       ? 'border-pick-line bg-sunken font-semibold text-ink-strong'
                       : 'border-rule bg-panel font-normal text-ink' }}
                          mb-1.5 mr-1.5 inline-flex cursor-pointer items-center rounded-full border px-3 py-1.5 text-sm
                          hover:border-green-line
                          peer/{{ $peer }}-focus-visible:outline peer/{{ $peer }}-focus-visible:outline-2
                          peer/{{ $peer }}-focus-visible:outline-offset-2 peer/{{ $peer }}-focus-visible:outline-ring">
                {{ $card->title }}
            </label>
        @endforeach

        {{-- The commit control. One per strip, not one per tab: a submit inside each label would
             put a button in the group's tab order and the arrow keys would stop meaning "move
             between forms". --}}
        <button type="submit"
                class="enamel mb-1.5 ml-1 rounded-full bg-chrome px-3 py-1 text-sm font-bold text-on-chrome">
            Open this form
        </button>

        @foreach ($cards as $index => $card)
            @php $peer = 't'.$index; @endphp
            {{-- `hidden` first so the panel is gone without CSS support; the peer rule outranks
                 it on specificity, so the checked form's panel is the one that opens. --}}
            <section id="form-panel-{{ $card->id }}" class="mt-4 hidden peer/{{ $peer }}-checked:block"
                     aria-label="{{ $card->title }}">
                @include($panelView, ['card' => $card, 'trainee' => $trainee])
            </section>
        @endforeach
    </form>
@endif

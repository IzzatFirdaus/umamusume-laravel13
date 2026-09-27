{{--
    Published over `pagination::tailwind` so the paginator renders from the design
    tokens. The framework view ships `bg-white` / `gray-*` / `blue-*` plus a
    `dark:` utility on every element, which is a G-19 violation (zero `dark:`
    utilities outside the theme override block) and leaves the dark theme with
    gray-200 chips on a charcoal page. Structure, ARIA and the `$elements`
    contract are the framework's; only the classes are ours. Re-check this file
    against vendor/laravel/framework/.../pagination/tailwind.blade.php on upgrade.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        <div class="flex items-center justify-between gap-2 sm:hidden">

            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex cursor-not-allowed items-center rounded-md border border-rule bg-sunken px-4 py-2 text-sm font-medium leading-5 text-ink-muted">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded-md border border-rule bg-raised px-4 py-2 text-sm font-medium leading-5 text-ink hover:bg-sunken focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center rounded-md border border-rule bg-raised px-4 py-2 text-sm font-medium leading-5 text-ink hover:bg-sunken focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span aria-disabled="true" class="inline-flex cursor-not-allowed items-center rounded-md border border-rule bg-sunken px-4 py-2 text-sm font-medium leading-5 text-ink-muted">
                    {!! __('pagination.next') !!}
                </span>
            @endif

        </div>

        <div class="hidden items-center justify-between gap-2 sm:flex">

            <div>
                <p class="text-sm leading-5 text-ink-muted">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium text-ink">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium text-ink">{{ $paginator->lastItem() }}</span>
                    @else
                        <span class="font-medium text-ink">{{ $paginator->count() }}</span>
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium text-ink">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="inline-flex rtl:flex-row-reverse rounded-md">

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span aria-hidden="true" class="inline-flex cursor-not-allowed items-center rounded-l-md border border-rule bg-sunken px-2 py-2 text-sm font-medium leading-5 text-ink-muted">
                                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="inline-flex items-center rounded-l-md border border-rule bg-raised px-2 py-2 text-sm font-medium leading-5 text-ink hover:bg-sunken focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                            <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="-ml-px inline-flex cursor-default items-center border border-rule bg-raised px-4 py-2 text-sm font-medium leading-5 text-ink-muted">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="-ml-px inline-flex cursor-default items-center border border-ink-faint bg-sunken px-4 py-2 text-sm font-bold leading-5 text-ink-strong">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="-ml-px inline-flex items-center border border-rule bg-raised px-4 py-2 text-sm font-medium leading-5 text-ink hover:bg-sunken focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="inline-flex items-center rounded-r-md border border-rule bg-raised px-2 py-2 text-sm font-medium leading-5 text-ink hover:bg-sunken focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green">
                            <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span aria-hidden="true" class="inline-flex cursor-not-allowed items-center rounded-r-md border border-rule bg-sunken px-2 py-2 text-sm font-medium leading-5 text-ink-muted">
                                <svg class="size-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif

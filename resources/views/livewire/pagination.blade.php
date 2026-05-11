

@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between pb-4 mt-4">
    <div class="flex justify-between flex-1 sm:hidden">
        <span>
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-3 py-2 text-sm text-stone-500 dark:text-slate-400 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 cursor-default leading-5 rounded-sm transition-none">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" dusk="previousPage.before" class="relative inline-flex items-center px-3 py-2 text-sm text-stone-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 leading-5 rounded-sm hover:text-stone-500 dark:hover:text-slate-400 focus:outline-none active:bg-stone-100 dark:active:bg-slate-700 transition-none">
                    {!! __('pagination.previous') !!}
                </button>
            @endif
        </span>

        <span>
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" dusk="nextPage.before" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium  text-stone-700 dark:text-slate-300 bg-white dark:text-slate-400 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 dark:border-slate-700 border border-stone-300 dark:border-slate-700 leading-5 rounded-sm hover:text-stone-500 dark:text-slate-400 focus:outline-none  focus:border-stone-500 dark:focus:border-slate-400 active:bg-stone-100 dark:bg-slate-800 active:text-stone-700 dark:text-slate-300 transition-none">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium  text-stone-500 dark:text-slate-400 bg-white dark:text-slate-400 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 dark:border-slate-700 border border-stone-300 dark:border-slate-700 cursor-default leading-5 rounded-sm">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </span>
    </div>

    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-stone-700 dark:text-slate-300 leading-5">
                <span>{!! __('Showing') !!}</span>
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                <span>{!! __('to') !!}</span>
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
                <span>{!! __('of') !!}</span>
                <span class="font-medium">{{ $paginator->total() }}</span>
                <span>{!! __('results') !!}</span>
            </p>
        </div>

        <div>
            <span class="relative z-0 inline-flex shadow-sm">
                <span>
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center px-3 py-2 text-sm text-stone-500 dark:text-slate-400 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 cursor-default rounded-sm-l-sm leading-5 transition-none" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <button wire:click="previousPage" dusk="previousPage.after" rel="prev" class="relative inline-flex items-center px-3 py-2 text-sm text-stone-500 dark:text-slate-400 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 rounded-sm-l-sm leading-5 hover:text-stone-400 dark:hover:text-slate-300 focus:z-10 focus:outline-none active:bg-stone-100 dark:active:bg-slate-700 transition-none" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif
                </span>

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true">
                            <span class="relative inline-flex items-center px-3 py-2 -ml-px text-sm text-stone-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 cursor-default leading-5 transition-none">{{ $element }}</span>
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-3 py-2 -ml-px text-sm text-white bg-stone-900 dark:bg-slate-200 dark:text-stone-900 border-stone-900 dark:border-slate-200 cursor-default leading-5 transition-none">{{ $page }}</span>
                                    </span>
                                @else
                                    <button  wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm text-stone-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 leading-5 hover:text-stone-500 dark:hover:text-slate-400 focus:z-10 focus:outline-none active:bg-stone-100 dark:active:bg-slate-700 transition-none" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </button>

                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach

                <span>
                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" dusk="nextPage.after" rel="next" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm text-stone-500 dark:text-slate-400 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 rounded-sm-r-sm leading-5 hover:text-stone-400 dark:hover:text-slate-300 focus:z-10 focus:outline-none active:bg-stone-100 dark:active:bg-slate-700 transition-none" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center px-3 py-2 -ml-px text-sm text-stone-500 dark:text-slate-400 bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-700 cursor-default rounded-sm-r-sm leading-5 transition-none" aria-hidden="true">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </span>
        </div>
    </div>
</nav>
@endif
</div>

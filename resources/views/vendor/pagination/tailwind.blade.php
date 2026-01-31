@if ($paginator->hasPages())
    <div
        class="flex items-center justify-between sm:justify-center w-full px-4 py-3 bg-white border-t border-gray-200 rounded-b-lg">
        <!-- Mobile View: Just Previous/Next buttons -->
        <div class="flex justify-between w-full sm:hidden">
            @if ($paginator->onFirstPage())
                <span
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 cursor-default rounded-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:text-gray-900 hover:bg-gray-50 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Anterior
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:text-gray-900 hover:bg-gray-50 transition ease-in-out duration-150">
                    Próximo
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            @else
                <span
                    class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-400 bg-white border border-gray-300 cursor-default rounded-md">
                    Próximo
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </span>
            @endif
        </div>

        <!-- Desktop View: Centered Text and Buttons -->
        <div class="hidden sm:flex sm:items-center sm:justify-center w-full gap-4">
            <!-- Text Info -->
            <div>
                <p class="text-sm text-gray-700">
                    Mostrando <span class="font-bold">{{ $paginator->firstItem() }}</span> a <span
                        class="font-bold">{{ $paginator->lastItem() }}</span> de <span
                        class="font-bold">{{ $paginator->total() }}</span> resultados
                </p>
            </div>

            <!-- Pagination Buttons -->
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span
                                class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 rounded-l-md cursor-default">
                                <span class="sr-only">Anterior</span>
                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Anterior
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                            class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 rounded-l-md transition ease-in-out duration-150">
                            <span class="sr-only">Anterior</span>
                            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Anterior
                        </a>
                    @endif

                    {{-- Pagination Elements (Page Numbers) --}}
                    {{-- Hidden on mobile, visible on desktop but maybe user just wants Prev/Next + Text? --}}
                    {{-- The user screenshots only showed Prev/Next buttons. Let's hide the numbers or show limited? --}}
                    {{-- The screenshot shows "Mostrando 1 de 162 < 1 2>" (Actually looking closely at crop 4 again) --}}
                        {{-- Actually, crop 4 of uploaded_media_0 shows: --}}
                        {{-- "Mostrando 1 de 162 clientes < Anterior Próximo>" --}}
                            {{-- Wait, the buttons are clearly labeled "Anterior" and "Próximo" with icons maybe? --}}
                            {{-- However, in standard pagination usually implies page numbers. --}}
                            {{-- The user's image shows: --}}
                            {{-- "< 1 2>" in the first image but " < Anterior Próximo> " in the second image? --}}
                                    {{-- Actually, crop 4 of the second set of images (uploaded_media_0) shows " < Anterior
                                        Próximo> ". --}}
                                        {{-- It does NOT show page numbers in the gap. --}}
                                        {{-- So I will HIDE page numbers for this specific "clean" design requested. --}}

                                        {{-- Next Page Link --}}
                                        @if ($paginator->hasMorePages())
                                            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                                                class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 rounded-r-md transition ease-in-out duration-150">
                                                <span class="sr-only">Próximo</span>
                                                Próximo
                                                <svg class="h-4 w-4 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        @else
                                            <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                                <span
                                                    class="relative inline-flex items-center px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 rounded-r-md cursor-default">
                                                    <span class="sr-only">Próximo</span>
                                                    Próximo
                                                    <svg class="h-4 w-4 ml-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </span>
                                            </span>
                                        @endif
                </nav>
            </div>
        </div>
    </div>
@endif
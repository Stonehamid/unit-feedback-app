@if($units->hasPages())
    <div class="px-4 py-3 ">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Info -->
            <div class="text-muted" style="font-size: 0.875rem;">
                Showing <span class="fw-semibold">{{ $units->firstItem() ?? 0 }}</span> to 
                <span class="fw-semibold">{{ $units->lastItem() ?? 0 }}</span> of 
                <span class="fw-semibold">{{ $units->total() }}</span> units
            </div>
            
            <!-- Pagination Navigation -->
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0 gap-1">
                    {{-- Previous Page Link --}}
                    <li class="page-item {{ $units->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-3 d-flex align-items-center justify-content-center {{ $units->onFirstPage() ? ' text-muted border-0' : 'border shadow-sm' }}" 
                           href="{{ $units->previousPageUrl() }}" 
                           aria-label="Previous" 
                           style="width: 36px; height: 36px; transition: all 0.2s ease;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                 fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                        </a>
                    </li>

                    {{-- Pagination Elements --}}
                    @php
                        $current = $units->currentPage();
                        $last = $units->lastPage();
                        $start = max(1, $current - 1);
                        $end = min($last, $current + 1);
                        
                        if($end - $start < 2) {
                            if($start == 1) {
                                $end = min($last, $start + 2);
                            } else {
                                $start = max(1, $end - 2);
                            }
                        }
                    @endphp

                    {{-- First Page Link --}}
                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link rounded-3 border shadow-sm d-flex align-items-center justify-content-center" 
                               href="{{ $units->url(1) }}" 
                               style="width: 36px; height: 36px; font-weight: 500;">
                                1
                            </a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link border-0 bg-transparent" 
                                      style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    ...
                                </span>
                            </li>
                        @endif
                    @endif

                    {{-- Page Number Links --}}
                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item">
                            <a class="page-link rounded-3 border shadow-sm d-flex align-items-center justify-content-center 
                                      {{ $i == $current ? 'bg-primary text-white border-primary' : 'bg-white text-dark' }}" 
                               href="{{ $units->url($i) }}" 
                               style="width: 36px; height: 36px; font-weight: 500;">
                                {{ $i }}
                            </a>
                        </li>
                    @endfor

                    {{-- Last Page Link --}}
                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link border-0 bg-transparent" 
                                      style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                    ...
                                </span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link rounded-3 border shadow-sm d-flex align-items-center justify-content-center" 
                               href="{{ $units->url($last) }}" 
                               style="width: 36px; height: 36px;">
                                {{ $last }}
                            </a>
                        </li>
                    @endif

                    {{-- Next Page Link --}}
                    <li class="page-item {{ $units->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link rounded-3 d-flex align-items-center justify-content-center {{ $units->hasMorePages() ? 'border shadow-sm' : 'bg-light text-muted border-0' }}" 
                           href="{{ $units->nextPageUrl() }}" 
                           aria-label="Next" 
                           style="width: 36px; height: 36px; transition: all 0.2s ease;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                 fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
@endif

<style>
    /* Pagination Styling */
    .pagination {
        --bs-pagination-color: #374151;
        --bs-pagination-bg: white;
        --bs-pagination-border-color: #d1d5db;
        --bs-pagination-hover-color: #1f2937;
        --bs-pagination-hover-bg: #f9fafb;
        --bs-pagination-hover-border-color: #9ca3af;
        --bs-pagination-focus-color: #1f2937;
        --bs-pagination-focus-bg: #f3f4f6;
        --bs-pagination-focus-box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        --bs-pagination-active-color: white;
        --bs-pagination-active-bg: #3b82f6;
        --bs-pagination-active-border-color: #3b82f6;
        --bs-pagination-disabled-color: #9ca3af;
        --bs-pagination-disabled-bg: #f9fafb;
        --bs-pagination-disabled-border-color: #e5e7eb;
    }

    .page-link {
        border: 1px solid #ffffff;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-link:not(.disabled):not(.active):hover {
        background-color: #f9fafb;
        border-color: #9ca3af;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .page-link:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .page-link.active {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
    }

    .page-link.disabled {
        background-color: #f9fafb;
        border-color: #e5e7eb;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .bg-light {
        background-color: #f9fafb !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
        
        .text-muted {
            text-align: center;
            width: 100%;
        }
        
        .pagination {
            justify-content: center;
            width: 100%;
        }
        
        .page-link {
            width: 32px !important;
            height: 32px !important;
            font-size: 0.75rem;
        }
    }
</style>
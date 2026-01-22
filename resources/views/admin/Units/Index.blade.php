@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 fw-bold mb-0" style="color: #1a1a1a;">Unit Inventory</h1>
            </div>
        </div>

        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div class="position-relative" style="flex: 1; max-width: 500px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="#6b7280" stroke-width="2" class="position-absolute top-50 translate-middle-y ms-3"
                        style="left: 0; z-index: 10;">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input type="text" id="searchInput" class="form-control ps-5 rounded-pill border shadow-sm"
                        placeholder="Search units by name, officer, or location..." value="{{ request('search') }}"
                        style="height: 44px; background-color: white; border-color: #d1d5db !important;">
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <button
                            class="btn btn-light border rounded-pill px-4 d-flex align-items-center gap-2 shadow-sm btn-filter"
                            type="button" id="filterDropdown" data-bs-toggle="dropdown"
                            style="height: 44px; background-color: white; border-color: #d1d5db;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                            </svg>
                            <span id="filterText" class="fw-medium">Filter</span>
                            @if(request('status') || request('type'))
                                <span class="badge bg-primary rounded-circle ms-1" style="width: 6px; height: 6px;"></span>
                            @endif
                        </button>

                        <div class="dropdown-menu p-3 border-0 shadow-lg rounded-3" style="min-width: 320px;">
                            <div class="mb-3">
                                <small class="text-uppercase text-muted fw-semibold mb-2 d-block"
                                    style="font-size: 0.7rem;">STATUS</small>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}"
                                        class="badge rounded-pill px-3 py-2 text-decoration-none 
                                                  {{ !request('status') ? 'bg-primary text-white' : 'bg-light text-dark border' }}">
                                        All
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['status' => 'OPEN']) }}"
                                        class="badge rounded-pill px-3 py-2 text-decoration-none 
                                                  {{ request('status') == 'OPEN' ? 'bg-success text-white' : 'bg-light text-dark border' }}">
                                        Open
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['status' => 'CLOSED']) }}"
                                        class="badge rounded-pill px-3 py-2 text-decoration-none 
                                                  {{ request('status') == 'CLOSED' ? 'bg-danger text-white' : 'bg-light text-dark border' }}">
                                        Closed
                                    </a>
                                    <a href="{{ request()->fullUrlWithQuery(['status' => 'FULL']) }}"
                                        class="badge rounded-pill px-3 py-2 text-decoration-none 
                                                  {{ request('status') == 'FULL' ? 'bg-warning text-white' : 'bg-light text-dark border' }}">
                                        Full
                                    </a>
                                </div>
                            </div>
                            <div class="mb-3">
                                <small class="text-uppercase text-muted fw-semibold mb-2 d-block"
                                    style="font-size: 0.7rem;">TYPE</small>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ request()->fullUrlWithQuery(['type' => '']) }}"
                                        class="badge rounded-pill px-3 py-2 text-decoration-none 
                                                  {{ !request('type') ? 'bg-primary text-white' : 'bg-light text-dark border' }}">
                                        All
                                    </a>
                                    @foreach($types ?? [] as $type)
                                        <a href="{{ request()->fullUrlWithQuery(['type' => $type]) }}"
                                            class="badge rounded-pill px-3 py-2 text-decoration-none 
                                                              {{ request('type') == $type ? 'bg-primary text-white' : 'bg-light text-dark border' }}">
                                            {{ $type }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-light flex-grow-1 rounded-2 border"
                                    onclick="clearFilters()">
                                    Clear All
                                </button>
                                <a href="{{ route('admin.units.view') }}"
                                    class="btn btn-sm flex-grow-1 rounded-2 btn-reset">
                                    Reset Filters
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button
                            class="btn btn-light border rounded-pill px-3 d-flex align-items-center gap-2 shadow-sm btn-export"
                            type="button" data-bs-toggle="dropdown"
                            style="height: 44px; background-color: white; border-color: #d1d5db;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            <span class="fw-medium">Export</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                            <li><a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                        <polyline points="10 9 9 9 8 9" />
                                    </svg>
                                    PDF Report
                                </a></li>
                            <li><a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <path d="M16 13H8" />
                                        <path d="M16 17H8" />
                                        <path d="M10 9H9H8" />
                                    </svg>
                                    Excel Sheet
                                </a></li>
                            <li><a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="6 9 6 2 18 2 18 9" />
                                        <path
                                            d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                        <rect x="6" y="14" width="12" height="8" />
                                    </svg>
                                    Print View
                                </a></li>
                        </ul>
                    </div>
                    
                    <a href="{{ route('admin.units.create') }}"
                        class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2 shadow-sm"
                        style="height: 44px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="white" stroke-width="2.5">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        <span class="fw-medium">Create Unit</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="me-2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="me-2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            {{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted text-uppercase" style="font-size: .75rem; letter-spacing: 0.5px;">
                        <th class="ps-4 py-3 fw-semibold">Unit</th>
                        <th class="py-3 fw-semibold">Type</th>
                        <th class="py-3 fw-semibold">Status</th>
                        <th class="py-3 fw-semibold">Capacity</th>
                        <th class="pe-4 py-3 fw-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @include('admin.units.partials.units_rows')
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-top">
            @include('admin.units.partials.pagination', ['units' => $units])
        </div>
    </div>

    <style>
        .btn {
            transition: all 0.2s ease;
            border-radius: 50px;
            font-weight: 500;
        }

        .btn-filter,
        .btn-export {
            color: #374151 !important;
            background-color: white;
            border: 1px solid #d1d5db;
        }

        .btn-filter:hover,
        .btn-export:hover,
        .btn-filter:active,
        .btn-export:active,
        .btn-filter:focus,
        .btn-export:focus,
        .btn-filter.show,
        .btn-export.show {
            color: #374151 !important;
            background-color: #f9fafb !important;
            border-color: #9ca3af !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        }

        .dropdown-toggle::after {
            color: #6b7280;
        }

        .btn-filter:hover .dropdown-toggle::after,
        .btn-export:hover .dropdown-toggle::after {
            color: #374151;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }

        #searchInput {
            border-color: #d1d5db !important;
            transition: all 0.2s ease;
        }

        #searchInput:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
            outline: none;
        }

        .dropdown-menu {
            border: 1px solid #e5e7eb;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.2s ease, transform 0.2s ease;
            display: block;
            pointer-events: none;
        }

        .dropdown-menu.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .filter-badge {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .filter-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(59, 130, 246, 0.02);
            --bs-table-hover-bg: rgba(59, 130, 246, 0.04);
        }

        .table> :not(:first-child) {
            border-top: 2px solid #e5e7eb;
        }

        .btn-reset {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            transition: all 0.2s ease;
        }

        .btn-reset:hover {
            background-color: #fecaca;
            color: #b91c1c;
            border-color: #fca5a5;
            transform: translateY(-1px);
        }

        .card {
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .dropdown-item {
            color: #374151 !important;
            transition: all 0.15s ease;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            color: #1f2937 !important;
            background-color: #f9fafb;
            transform: translateX(2px);
        }

        .btn-filter.show,
        .btn-export.show {
            background-color: #f3f4f6 !important;
        }

        @media (max-width: 768px) {
            .d-flex.justify-content-between.align-items-center.gap-3 {
                flex-direction: column;
                gap: 1rem !important;
            }

            .position-relative {
                max-width: 100% !important;
                width: 100%;
            }

            .d-flex.align-items-center.gap-2 {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .btn span {
                display: inline-block;
            }
        }

        /* Tambahkan di file CSS */
        .badge-status {
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .badge-status-open {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }

        .badge-status-closed {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .badge-status-full {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }
    </style>

    <script>
        let searchTimeout;
        document.getElementById('searchInput')?.addEventListener('input', function (e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 500);
        });

        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            const url = new URL(window.location.href);

            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            } else {
                url.searchParams.delete('search');
            }

            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }

        function clearFilters() {
            const url = new URL(window.location.href);
            url.searchParams.delete('status');
            url.searchParams.delete('type');
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateFilterIndicator();

            document.querySelectorAll('.filter-badge').forEach(badge => {
                badge.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.location.href = this.href;
                });
            });

            const filterBtn = document.querySelector('.btn-filter');
            const exportBtn = document.querySelector('.btn-export');

            if (filterBtn) {
                filterBtn.addEventListener('click', function () {
                    this.style.color = '#374151';
                });

                filterBtn.addEventListener('show.bs.dropdown', function () {
                    this.style.color = '#374151';
                });
            }

            if (exportBtn) {
                exportBtn.addEventListener('click', function () {
                    this.style.color = '#374151';
                });

                exportBtn.addEventListener('show.bs.dropdown', function () {
                    this.style.color = '#374151';
                });
            }

            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const menu = dropdown.querySelector('.dropdown-menu');

                toggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const isShown = menu.classList.contains('show');

                    document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                        if (openMenu !== menu) {
                            openMenu.classList.remove('show');
                        }
                    });

                    if (!isShown) {
                        menu.classList.add('show');
                    } else {
                        menu.classList.remove('show');
                    }
                });
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
        });

        function updateFilterIndicator() {
            const filterBtn = document.getElementById('filterDropdown');
            const filterText = document.getElementById('filterText');
            const status = new URLSearchParams(window.location.search).get('status');
            const type = new URLSearchParams(window.location.search).get('type');

            if (!filterBtn || !filterText) return;

            let indicator = filterBtn.querySelector('.badge');

            if (status || type) {
                let text = 'Filter';
                if (status && type) {
                    text += `: ${getStatusLabel(status)}, ${type}`;
                } else if (status) {
                    text += `: ${getStatusLabel(status)}`;
                } else if (type) {
                    text += `: ${type}`;
                }

                filterText.textContent = text;

                if (!indicator) {
                    indicator = document.createElement('span');
                    indicator.className = 'badge bg-primary rounded-circle ms-1';
                    indicator.style.cssText = 'width: 6px; height: 6px;';
                    filterBtn.appendChild(indicator);
                }
            } else {
                filterText.textContent = 'Filter';

                if (indicator) {
                    indicator.remove();
                }
            }
        }

        function getStatusLabel(status) {
            const labels = {
                'OPEN': 'Open',
                'CLOSED': 'Closed',
                'FULL': 'Full'
            };
            return labels[status] || status;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const style = document.createElement('style');
            style.textContent = `
                .btn-filter:hover span,
                .btn-filter:active span,
                .btn-filter.show span,
                .btn-export:hover span,
                .btn-export:active span,
                .btn-export.show span {
                    color: #374151 !important;
                }

                .btn-filter:hover svg,
                .btn-filter:active svg,
                .btn-filter.show svg,
                .btn-export:hover svg,
                .btn-export:active svg,
                .btn-export.show svg {
                    stroke: #374151 !important;
                }

                .btn-filter,
                .btn-export {
                    color: #374151 !important;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
@endsection
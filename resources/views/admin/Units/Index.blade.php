@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-0 px-md-4">
        <!-- Header yang lebih clean -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1 class="h4 fw-bold mb-1">Units</h1>
                <p class="text-muted mb-0" style="font-size: 13px;">Total {{ $units->total() }} unit</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Search Bar Compact -->
                <div class="d-none d-md-flex align-items-center">
                    <div class="input-group input-group-sm rounded-3" style="width: 200px;">
                        <span class="input-group-text bg-transparent border-end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                 fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control form-control-sm border-start-0" 
                               placeholder="Cari unit..." id="quickSearch">
                    </div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm rounded-3 px-3 d-flex align-items-center gap-2" 
                            type="button" data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                             fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 8l6 6 6-6"/>
                        </svg>
                        Ekspor
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">PDF</a></li>
                        <li><a class="dropdown-item" href="#">Excel</a></li>
                        <li><a class="dropdown-item" href="#">Print</a></li>
                    </ul>
                </div>
                
                <a href="{{ route('admin.units.create') }}" 
                   class="btn btn-primary btn-sm rounded-3 px-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Tambah Unit
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" 
                         fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" 
                         fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filter Section yang Lebih Modern -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-link text-dark p-0 d-flex align-items-center" 
                                type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                 fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                            </svg>
                            <span class="fw-medium">Filter</span>
                        </button>
                        @if(request('status') || request('type') || request('search'))
                            <span class="badge bg-light text-dark rounded-pill px-2 py-1" style="font-size: 11px;">
                                Filter Aktif
                                <a href="{{ route('admin.units.view') }}" class="text-danger ms-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="3">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </a>
                            </span>
                        @endif
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm rounded-3 px-3 d-flex align-items-center gap-1" 
                                    type="button" data-bs-toggle="dropdown">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                     fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7"/>
                                    <rect x="14" y="3" width="7" height="7"/>
                                    <rect x="3" y="14" width="7" height="7"/>
                                    <rect x="14" y="14" width="7" height="7"/>
                                </svg>
                                Tampilan
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Semua Kolom</a></li>
                                <li><a class="dropdown-item" href="#">Kolom Minimal</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">Simpan Tampilan</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="collapse {{ request('status') || request('type') || request('search') ? 'show' : '' }}" 
                     id="filterCollapse">
                    <form method="GET" action="{{ route('admin.units.view') }}" class="pt-2">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0" style="font-size: 12px;">
                                        Status
                                    </span>
                                    <select name="status" class="form-select form-select-sm border-start-0 rounded-end">
                                        <option value="">Semua</option>
                                        <option value="OPEN" {{ request('status') == 'OPEN' ? 'selected' : '' }}>Buka</option>
                                        <option value="CLOSED" {{ request('status') == 'CLOSED' ? 'selected' : '' }}>Tutup</option>
                                        <option value="FULL" {{ request('status') == 'FULL' ? 'selected' : '' }}>Penuh</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0" style="font-size: 12px;">
                                        Tipe
                                    </span>
                                    <select name="type" class="form-select form-select-sm border-start-0 rounded-end">
                                        <option value="">Semua</option>
                                        @foreach($types ?? [] as $type)
                                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control form-control-sm" 
                                           placeholder="Cari nama, officer, atau lokasi..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm rounded-3 flex-grow-1">
                                        Terapkan
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" 
                                            onclick="window.location.href='{{ route('admin.units.view') }}'">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%;" class="ps-4">Unit</th>
                            <th style="width: 15%;" class="text-center">Tipe</th>
                            <th style="width: 15%;" class="text-center">Rating</th>
                            <th style="width: 15%;" class="text-center">Status</th>
                            <th style="width: 20%;" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($units->count() > 0)
                            @foreach($units as $unit)
                                @php
                                    // Status dengan kombinasi
                                    $statusColor = '';
                                    $statusIcon = '';
                                    
                                    switch($unit->status) {
                                        case 'OPEN':
                                            $statusColor = 'success';
                                            $statusIcon = '✓';
                                            break;
                                        case 'CLOSED':
                                            $statusColor = 'danger';
                                            $statusIcon = '✗';
                                            break;
                                        case 'FULL':
                                            $statusColor = 'warning';
                                            $statusIcon = '⏳';
                                            break;
                                        default:
                                            $statusColor = 'secondary';
                                            $statusIcon = '•';
                                    }
                                    
                                    $activeStatus = $unit->is_active ? 'Aktif' : 'Nonaktif';
                                    $activeColor = $unit->is_active ? 'success' : 'secondary';
                                    
                                    // Photo URL
                                    $photoUrl = $unit->photo 
                                        ? asset('storage/' . $unit->photo)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($unit->name) . '&background=3b82f6&color=ffffff&size=40';
                                    
                                    // Rating stars
                                    $fullStars = floor($unit->avg_rating);
                                    $halfStar = ($unit->avg_rating - $fullStars) >= 0.5;
                                    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                @endphp
                                
                                <tr class="align-middle">
                                    <!-- Unit Column -->
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="{{ $photoUrl }}" width="40" height="40" 
                                                 class="rounded-circle object-fit-cover border">
                                            <div>
                                                <div class="fw-medium mb-1">{{ $unit->name }}</div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-muted" style="font-size: 12px;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" 
                                                             fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                                            <circle cx="12" cy="7" r="4"/>
                                                        </svg>
                                                        {{ $unit->officer_name }}
                                                    </span>
                                                    <span class="text-muted" style="font-size: 12px;">•</span>
                                                    <span class="text-muted" style="font-size: 12px;">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" 
                                                             fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                                            <circle cx="12" cy="10" r="3"/>
                                                        </svg>
                                                        {{ Str::limit($unit->location, 20) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Type Column -->
                                    <td class="text-center">
                                        <span class="badge bg-light-secondary text-secondary rounded-pill px-3" 
                                              style="font-size: 11px;">
                                            {{ $unit->type }}
                                        </span>
                                    </td>
                                    
                                    <!-- Rating Column -->
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="d-flex align-items-center">
                                                    @for($i = 0; $i < $fullStars; $i++)
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                                             fill="#FFC107" stroke="#FFC107" stroke-width="1" class="me-1">
                                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                                        </svg>
                                                    @endfor
                                                    
                                                    @if($halfStar)
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                                             fill="#FFC107" stroke="#FFC107" stroke-width="1" class="me-1">
                                                            <defs>
                                                                <linearGradient id="half-{{ $unit->id }}">
                                                                    <stop offset="50%" stop-color="#FFC107"/>
                                                                    <stop offset="50%" stop-color="transparent"/>
                                                                </linearGradient>
                                                            </defs>
                                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" 
                                                                     fill="url(#half-{{ $unit->id }})"/>
                                                        </svg>
                                                    @endif
                                                    
                                                    @for($i = 0; $i < $emptyStars; $i++)
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                                             fill="none" stroke="#FFC107" stroke-width="1" class="me-1">
                                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                                        </svg>
                                                    @endfor
                                                </div>
                                            </div>
                                            <span class="fw-medium" style="font-size: 12px;">
                                                {{ number_format($unit->avg_rating, 1) }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Status Column -->
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <span class="badge bg-light-{{ $statusColor }} text-{{ $statusColor }} 
                                                   rounded-pill px-3 d-flex align-items-center gap-1" style="font-size: 11px;">
                                                <span>{{ $statusIcon }}</span>
                                                {{ $unit->status == 'OPEN' ? 'Buka' : ($unit->status == 'CLOSED' ? 'Tutup' : 'Penuh') }}
                                            </span>
                                            <span class="badge bg-light-{{ $activeColor }} text-{{ $activeColor }} 
                                                   rounded-pill px-2" style="font-size: 10px;">
                                                {{ $activeStatus }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Actions Column -->
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('admin.units.show', $unit->id) }}" 
                                               class="btn btn-sm btn-outline-primary rounded-3 px-3 d-flex align-items-center gap-1"
                                               title="Detail">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" 
                                                     fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                                <span class="d-none d-md-inline">Detail</span>
                                            </a>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0 rounded-3" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                                         fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="1"/>
                                                        <circle cx="12" cy="5" r="1"/>
                                                        <circle cx="12" cy="19" r="1"/>
                                                    </svg>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2" 
                                                           href="{{ route('admin.units.edit', $unit->id) }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                                                 fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                            </svg>
                                                            Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('admin.units.destroy', $unit->id) }}" method="POST" 
                                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                                                     fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                                </svg>
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="py-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" 
                                             fill="none" stroke="currentColor" stroke-width="1.5" class="text-muted mb-3">
                                            <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                            <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/>
                                            <path d="M9 9h1"/>
                                            <path d="M9 13h6"/>
                                            <path d="M9 17h6"/>
                                        </svg>
                                        <h6 class="fw-medium mb-2">Tidak ada unit ditemukan</h6>
                                        <p class="text-muted mb-3" style="font-size: 14px;">
                                            @if(request('status') || request('type') || request('search'))
                                                Coba ubah filter pencarian Anda
                                            @else
                                                Mulai dengan menambahkan unit pertama
                                            @endif
                                        </p>
                                        @if(request('status') || request('type') || request('search'))
                                            <a href="{{ route('admin.units.view') }}" class="btn btn-outline-primary btn-sm rounded-3">
                                                Reset Filter
                                            </a>
                                        @else
                                            <a href="{{ route('admin.units.create') }}" class="btn btn-primary btn-sm rounded-3">
                                                Tambah Unit
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination yang Diperbaiki -->
            @if($units->hasPages())
                <div class="card-footer border-0 bg-transparent py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <div class="mb-2 mb-md-0">
                            <p class="text-muted mb-0" style="font-size: 13px;">
                                Menampilkan {{ $units->firstItem() ?? 0 }} - {{ $units->lastItem() ?? 0 }} 
                                dari {{ $units->total() }} unit
                            </p>
                        </div>
                        
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                {{-- Previous Page Link --}}
                                <li class="page-item {{ $units->onFirstPage() ? 'disabled' : '' }}">
                                    <a class="page-link rounded-3 border-0 {{ $units->onFirstPage() ? 'text-muted' : '' }}" 
                                       href="{{ $units->previousPageUrl() }}" 
                                       aria-label="Previous" style="padding: 0.25rem 0.5rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                             fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="15 18 9 12 15 6"/>
                                        </svg>
                                    </a>
                                </li>

                                {{-- Pagination Elements --}}
                                @php
                                    $current = $units->currentPage();
                                    $last = $units->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                    
                                    if($end - $start < 4) {
                                        if($start == 1) {
                                            $end = min($last, $start + 4);
                                        } else {
                                            $start = max(1, $end - 4);
                                        }
                                    }
                                @endphp

                                {{-- First Page Link --}}
                                @if($start > 1)
                                    <li class="page-item">
                                        <a class="page-link rounded-3 border-0" 
                                           href="{{ $units->url(1) }}" 
                                           style="padding: 0.25rem 0.5rem;">1</a>
                                    </li>
                                    @if($start > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link border-0" style="padding: 0.25rem 0.5rem;">...</span>
                                        </li>
                                    @endif
                                @endif

                                {{-- Page Number Links --}}
                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                        <a class="page-link rounded-3 border-0 {{ $i == $current ? 'bg-primary text-white' : '' }}" 
                                           href="{{ $units->url($i) }}" 
                                           style="padding: 0.25rem 0.5rem;">
                                            {{ $i }}
                                        </a>
                                    </li>
                                @endfor

                                {{-- Last Page Link --}}
                                @if($end < $last)
                                    @if($end < $last - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link border-0" style="padding: 0.25rem 0.5rem;">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link rounded-3 border-0" 
                                           href="{{ $units->url($last) }}" 
                                           style="padding: 0.25rem 0.5rem;">
                                            {{ $last }}
                                        </a>
                                    </li>
                                @endif

                                {{-- Next Page Link --}}
                                <li class="page-item {{ $units->hasMorePages() ? '' : 'disabled' }}">
                                    <a class="page-link rounded-3 border-0 {{ $units->hasMorePages() ? '' : 'text-muted' }}" 
                                       href="{{ $units->nextPageUrl() }}" 
                                       aria-label="Next" style="padding: 0.25rem 0.5rem;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
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
        </div>
    </div>

    <style>
        .table th {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 2px solid #e9ecef;
            padding: 12px 16px;
        }
        
        .table td {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.03);
        }
        
        .card {
            border: 1px solid rgba(0,0,0,0.08);
        }
        
        .pagination .page-link {
            min-width: 32px;
            text-align: center;
            margin: 0 2px;
        }
        
        .pagination .page-item.active .page-link {
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }
        
        .input-group-text {
            background-color: transparent;
        }
        
        .form-control, .form-select {
            border-color: rgba(0,0,0,0.1);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.8125rem;
        }
        
        .badge {
            font-weight: 500;
        }
        
        /* Collapse animation */
        .collapse {
            transition: all 0.3s ease;
        }
        
        /* Quick search focus */
        #quickSearch:focus {
            width: 250px;
            transition: width 0.3s ease;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table-responsive {
                border-radius: 0;
            }
            
            .table th, .table td {
                padding: 12px 8px;
            }
            
            .btn span {
                display: none;
            }
            
            .btn svg {
                margin: 0;
            }
        }
    </style>

    <script>
        // Quick search functionality
        document.getElementById('quickSearch')?.addEventListener('keypress', function(e) {
            if(e.key === 'Enter') {
                const searchTerm = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('search', searchTerm);
                window.location.href = url.toString();
            }
        });
        
        // Auto-close filter collapse on mobile after apply
        if(window.innerWidth < 768) {
            document.querySelectorAll('#filterCollapse form button[type="submit"]').forEach(button => {
                button.addEventListener('click', function() {
                    setTimeout(() => {
                        const collapse = document.getElementById('filterCollapse');
                        const bsCollapse = new bootstrap.Collapse(collapse, {
                            toggle: false
                        });
                        bsCollapse.hide();
                    }, 500);
                });
            });
        }
    </script>
@endsection
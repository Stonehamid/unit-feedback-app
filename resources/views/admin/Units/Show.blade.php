@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold mb-0" style="color: #1a1a1a;">Unit Details</h1>
            <p class="text-muted mb-0">View detailed information about this unit</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center" role="alert" style="background-color: #f0fdf4; border-color: #bbf7d0; color: #15803d;">
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
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center" role="alert" style="background-color: #fef2f2; border-color: #fecaca; color: #dc2626;">
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

    <div class="row">
        <div class="col-lg-8">
            <div class="card border shadow-sm rounded-4 mb-4" style="border-color: #e5e7eb !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-4 mb-4">
                        @if($unit->photo)
                            <div class="rounded-3 overflow-hidden border" style="width: 120px; height: 120px; background-color: #f9fafb; border-color: #e5e7eb !important;">
                                <img src="{{ asset('storage/' . $unit->photo) }}" 
                                     alt="{{ $unit->name }}" 
                                     class="w-100 h-100 object-fit-cover">
                            </div>
                        @else
                            <div class="rounded-3 d-flex align-items-center justify-content-center border" 
                                 style="width: 120px; height: 120px; background-color: #f9fafb; border-color: #e5e7eb !important;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" 
                                     fill="none" stroke="#9ca3af" stroke-width="1">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <polyline points="21 15 16 10 5 21" />
                                </svg>
                            </div>
                        @endif

                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h2 class="h4 fw-bold mb-1" style="color: #1a1a1a;">{{ $unit->name }}</h2>
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="badge rounded-pill px-3 py-1 
                                              @if($unit->status == 'OPEN') bg-success
                                              @elseif($unit->status == 'CLOSED') bg-danger
                                              @else bg-warning text-dark @endif">
                                            {{ $unit->status }}
                                        </span>
                                        @if($unit->is_active)
                                            <span class="badge rounded-pill px-3 py-1" style="background-color: #0ea5e9; color: white;">Active</span>
                                        @else
                                            <span class="badge rounded-pill px-3 py-1 bg-secondary">Inactive</span>
                                        @endif
                                        @if($unit->featured)
                                            <span class="badge rounded-pill px-3 py-1" style="background-color: #8b5cf6; color: white;">Featured</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted mb-1" style="font-size: 0.875rem;">Created</div>
                                    <div class="fw-medium">{{ $unit->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted mb-1" style="font-size: 0.875rem;">Officer</div>
                                    <div class="fw-medium d-flex align-items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                             fill="none" stroke="currentColor" stroke-width="2" class="text-muted">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                        {{ $unit->officer_name }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted mb-1" style="font-size: 0.875rem;">Type</div>
                                    <div class="fw-medium d-flex align-items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                             fill="none" stroke="currentColor" stroke-width="2" class="text-muted">
                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                        </svg>
                                        {{ $unit->type }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3" style="color: #1a1a1a;">Description</h6>
                        <div class="p-3 rounded-3 border" style="background-color: #f9fafb; border-color: #e5e7eb !important;">
                            {{ $unit->description }}
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border" style="border-color: #e5e7eb !important;">
                                <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2" style="color: #1a1a1a;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                        <polyline points="22,6 12,13 2,6" />
                                    </svg>
                                    Contact Information
                                </h6>
                                <div class="space-y-2">
                                    @if($unit->contact_email)
                                        <div class="d-flex align-items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                                 fill="none" stroke="#6b7280" stroke-width="2">
                                                <circle cx="12" cy="12" r="4" />
                                                <path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94" />
                                            </svg>
                                            <span class="text-muted">Email:</span>
                                            <a href="mailto:{{ $unit->contact_email }}" class="fw-medium text-decoration-none" style="color: #3b82f6;">
                                                {{ $unit->contact_email }}
                                            </a>
                                        </div>
                                    @endif
                                    @if($unit->contact_phone)
                                        <div class="d-flex align-items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" 
                                                 fill="none" stroke="#6b7280" stroke-width="2">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                            </svg>
                                            <span class="text-muted">Phone:</span>
                                            <span class="fw-medium">{{ $unit->contact_phone }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border" style="border-color: #e5e7eb !important;">
                                <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2" style="color: #1a1a1a;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    Location Details
                                </h6>
                                <div class="d-flex align-items-start gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                         fill="none" stroke="#6b7280" stroke-width="2" class="mt-1">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <div>
                                        <div class="fw-medium mb-1">{{ $unit->location }}</div>
                                        <div class="text-muted small">Unit location address</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($unit->opening_time || $unit->closing_time)
            <div class="card border shadow-sm rounded-4 mb-4" style="border-color: #e5e7eb !important;">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2" style="color: #1a1a1a;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                             fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        Operating Hours
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border" style="border-color: #e5e7eb !important;">
                                <div class="text-muted mb-1" style="font-size: 0.875rem;">Opening Time</div>
                                <div class="fw-medium d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                         fill="none" stroke="#6b7280" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    {{ $unit->opening_time ? \Carbon\Carbon::parse($unit->opening_time)->format('h:i A') : 'Not Set' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border" style="border-color: #e5e7eb !important;">
                                <div class="text-muted mb-1" style="font-size: 0.875rem;">Closing Time</div>
                                <div class="fw-medium d-flex align-items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                         fill="none" stroke="#6b7280" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    {{ $unit->closing_time ? \Carbon\Carbon::parse($unit->closing_time)->format('h:i A') : 'Not Set' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border shadow-sm rounded-4 mb-4" style="border-color: #e5e7eb !important;">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2" style="color: #1a1a1a;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                             fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="20" x2="18" y2="10" />
                            <line x1="12" y1="20" x2="12" y2="4" />
                            <line x1="6" y1="20" x2="6" y2="14" />
                        </svg>
                        Statistics
                    </h6>
                    <div class="space-y-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">Average Rating</div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="fw-bold">{{ number_format($unit->avg_rating, 1) }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                     fill="#f59e0b" stroke="#f59e0b" stroke-width="1">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                </svg>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">Total Ratings</div>
                            <div class="fw-medium">{{ $unit->ratings_count ?? 0 }}</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">Status Updated</div>
                            <div class="fw-medium">{{ $unit->status_changed_at ? \Carbon\Carbon::parse($unit->status_changed_at)->format('M d, Y') : 'Never' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border shadow-sm rounded-4 mb-4" style="border-color: #e5e7eb !important;">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2" style="color: #1a1a1a;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                             fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4M12 8h.01" />
                        </svg>
                        Quick Actions
                    </h6>
                    <div class="d-grid gap-2">
                        <div class="btn-group w-100" role="group">
                            <button type="button" 
                                    class="btn btn-outline-success border 
                                           {{ $unit->status == 'OPEN' ? 'active' : '' }}"
                                    data-status="OPEN"
                                    onclick="updateStatus('OPEN')"
                                    style="border-color: #d1d5db;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                     fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                                Open
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-danger border 
                                           {{ $unit->status == 'CLOSED' ? 'active' : '' }}"
                                    data-status="CLOSED"
                                    onclick="updateStatus('CLOSED')"
                                    style="border-color: #d1d5db;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                     fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="15" y1="9" x2="9" y2="15" />
                                    <line x1="9" y1="9" x2="15" y2="15" />
                                </svg>
                                Closed
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-warning border 
                                           {{ $unit->status == 'FULL' ? 'active' : '' }}"
                                    data-status="FULL"
                                    onclick="updateStatus('FULL')"
                                    style="border-color: #d1d5db;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                     fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                Full
                            </button>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" 
                                    class="btn btn-outline-primary flex-grow-1 rounded-pill border"
                                    onclick="toggleActive()"
                                    style="border-color: #d1d5db;">
                                @if($unit->is_active)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M8 12h8" />
                                    </svg>
                                    Deactivate
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="8" x2="12" y2="16" />
                                        <line x1="8" y1="12" x2="16" y2="12" />
                                    </svg>
                                    Activate
                                @endif
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-outline-info flex-grow-1 rounded-pill border"
                                    onclick="toggleFeatured()"
                                    style="border-color: #d1d5db;">
                                @if($unit->featured)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                    Unfeature
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                    Feature
                                @endif
                            </button>
                        </div>

                        <button type="button" 
                                class="btn btn-outline-danger rounded-pill d-flex align-items-center justify-content-center gap-2 border"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal"
                                style="border-color: #d1d5db;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                                 fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6" />
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                <line x1="10" y1="11" x2="10" y2="17" />
                                <line x1="14" y1="11" x2="14" y2="17" />
                            </svg>
                            Delete Unit
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border shadow-sm rounded-4 mb-4" style="border-color: #e5e7eb !important;">
                <div class="card-body p-4">
                    <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2" style="color: #1a1a1a;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                             fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        System Information
                    </h6>
                    <div class="space-y-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Created</span>
                            <span class="fw-medium">{{ $unit->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Last Updated</span>
                            <span class="fw-medium">{{ $unit->updated_at->format('M d, Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Database ID</span>
                            <span class="fw-medium">#{{ str_pad($unit->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>

                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.units.edit', $unit->id) }}" 
                   class="btn flex-grow-1 rounded-pill d-flex align-items-center justify-content-center gap-2"
                   style="background-color: #f59e0b; border-color: #f59e0b; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    <span class="fw-medium">Edit Unit</span>
                </a>
                <a href="{{ route('admin.units.view') }}" 
                   class="btn btn-outline-secondary flex-grow-1 rounded-pill d-flex align-items-center justify-content-center gap-2"
                   style="border-color: #d1d5db;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    <span class="fw-medium">Back to List</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3" style="border: 1px solid #e5e7eb !important;">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded-circle" style="background-color: #fef2f2;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" 
                             fill="none" stroke="#dc2626" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </div>
                    <h5 class="modal-title fw-semibold" style="color: #1a1a1a;">Confirm Delete</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="mb-0">Are you sure you want to delete <strong>{{ $unit->name }}</strong>? This action cannot be undone and will permanently remove all associated data.</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal" style="border-color: #d1d5db;">Cancel</button>
                <form method="POST" action="{{ route('admin.units.destroy', $unit->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4" style="background-color: #dc2626; border-color: #dc2626;">
                        Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f9fafb;
    }

    .card {
        border: 1px solid #e5e7eb !important;
        background-color: white;
    }

    .bg-success {
        background-color: #10b981 !important;
    }
    
    .bg-danger {
        background-color: #ef4444 !important;
    }
    
    .bg-warning {
        background-color: #f59e0b !important;
    }

    .btn {
        transition: all 0.15s ease;
    }

    .btn:hover {
        transform: none;
    }

    .btn-outline-primary:hover {
        background-color: #eff6ff;
        border-color: #3b82f6;
        color: #1d4ed8;
    }

    .btn-outline-success:hover {
        background-color: #f0fdf4;
        border-color: #10b981;
        color: #047857;
    }

    .btn-outline-danger:hover {
        background-color: #fef2f2;
        border-color: #ef4444;
        color: #b91c1c;
    }

    .btn-outline-warning:hover {
        background-color: #fefce8;
        border-color: #f59e0b;
        color: #a16207;
    }

    .btn-outline-info:hover {
        background-color: #f0f9ff;
        border-color: #0ea5e9;
        color: #0284c7;
    }

    .btn-outline-secondary:hover {
        background-color: #f9fafb;
        border-color: #6b7280;
        color: #374151;
    }

    .btn-group .btn.active {
        background-color: #3b82f6 !important;
        color: white !important;
        border-color: #3b82f6 !important;
    }

    .btn-group .btn.active.btn-outline-success {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
    }

    .btn-group .btn.active.btn-outline-danger {
        background-color: #ef4444 !important;
        border-color: #ef4444 !important;
    }

    .btn-group .btn.active.btn-outline-warning {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #1a1a1a !important;
    }

    a {
        color: #3b82f6;
    }

    a:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    .text-muted svg {
        stroke: #6b7280;
    }

    .alert-success {
        background-color: #f0fdf4;
        border-color: #bbf7d0;
        color: #15803d;
    }

    .alert-danger {
        background-color: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    svg[fill="#f59e0b"] {
        fill: #f59e0b !important;
        stroke: #f59e0b !important;
    }

    @media (max-width: 768px) {
        .d-flex.justify-content-between.align-items-center.mb-4 {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .col-lg-8, .col-lg-4 {
            width: 100% !important;
        }

        .btn-group {
            flex-wrap: wrap;
        }

        .btn-group .btn {
            flex: 1;
            min-width: 100px;
        }
    }
</style>

<script>
    function updateStatus(status) {
        if(confirm(`Are you sure you want to change status to ${status}?`)) {
            fetch('{{ route("admin.units.update", $unit->id) }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    }

    function toggleActive() {
        const newStatus = !{{ $unit->is_active ? 'true' : 'false' }};
        
        fetch('{{ route("admin.units.update", $unit->id) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                is_active: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Failed to update status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }

    function toggleFeatured() {
        const newStatus = !{{ $unit->featured ? 'true' : 'false' }};
        
        fetch('{{ route("admin.units.update", $unit->id) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                featured: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Failed to update featured status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const statusButtons = document.querySelectorAll('.btn-group .btn');
        statusButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                statusButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
</script>
@endsection
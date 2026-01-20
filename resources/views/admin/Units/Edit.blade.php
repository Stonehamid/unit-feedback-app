@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold mb-0" style="color: #1a1a1a;">Edit Unit</h1>
            <p class="text-muted mb-0">Update unit information</p>
        </div>
        <a href="{{ route('admin.units.view') }}" 
           class="btn btn-outline-secondary rounded-pill px-4 d-flex align-items-center gap-2"
           style="height: 44px; border-color: #d1d5db;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            <span class="fw-medium">Back to Units</span>
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center" role="alert" style="background-color: #fef2f2; border-color: #fecaca; color: #dc2626;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" class="me-2">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border shadow-sm rounded-4" style="border-color: #e5e7eb !important;">
        <div class="card-body p-4">
            <form action="{{ route('admin.units.update', $unit->id) }}" method="POST" enctype="multipart/form-data" id="editUnitForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3" style="color: #1a1a1a;">Basic Information</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label fw-medium mb-2">Unit Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control rounded-3 border @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name', $unit->name) }}"
                                               placeholder="Enter unit name"
                                               style="height: 48px; border-color: #d1d5db;">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="officer_name" class="form-label fw-medium mb-2">Officer Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control rounded-3 border @error('officer_name') is-invalid @enderror" 
                                               id="officer_name" 
                                               name="officer_name" 
                                               value="{{ old('officer_name', $unit->officer_name) }}"
                                               placeholder="Enter officer's name"
                                               style="height: 48px; border-color: #d1d5db;">
                                        @error('officer_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type" class="form-label fw-medium mb-2">Unit Type <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control rounded-3 border @error('type') is-invalid @enderror" 
                                               id="type" 
                                               name="type" 
                                               value="{{ old('type', $unit->type) }}"
                                               placeholder="e.g., Storage, Warehouse, Office"
                                               style="height: 48px; border-color: #d1d5db;">
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="location" class="form-label fw-medium mb-2">Location <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control rounded-3 border @error('location') is-invalid @enderror" 
                                               id="location" 
                                               name="location" 
                                               value="{{ old('location', $unit->location) }}"
                                               placeholder="Enter unit location"
                                               style="height: 48px; border-color: #d1d5db;">
                                        @error('location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3" style="color: #1a1a1a;">Status & Contact</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status" class="form-label fw-medium mb-2">Status <span class="text-danger">*</span></label>
                                        <select class="form-select rounded-3 border @error('status') is-invalid @enderror" 
                                                id="status" 
                                                name="status"
                                                style="height: 48px; border-color: #d1d5db;">
                                            <option value="OPEN" {{ old('status', $unit->status) == 'OPEN' ? 'selected' : '' }}>Open</option>
                                            <option value="CLOSED" {{ old('status', $unit->status) == 'CLOSED' ? 'selected' : '' }}>Closed</option>
                                            <option value="FULL" {{ old('status', $unit->status) == 'FULL' ? 'selected' : '' }}>Full</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_email" class="form-label fw-medium mb-2">Contact Email</label>
                                        <input type="email" 
                                               class="form-control rounded-3 border @error('contact_email') is-invalid @enderror" 
                                               id="contact_email" 
                                               name="contact_email" 
                                               value="{{ old('contact_email', $unit->contact_email) }}"
                                               placeholder="Enter contact email"
                                               style="height: 48px; border-color: #d1d5db;">
                                        @error('contact_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_phone" class="form-label fw-medium mb-2">Contact Phone</label>
                                        <input type="text" 
                                               class="form-control rounded-3 border @error('contact_phone') is-invalid @enderror" 
                                               id="contact_phone" 
                                               name="contact_phone" 
                                               value="{{ old('contact_phone', $unit->contact_phone) }}"
                                               placeholder="Enter contact phone"
                                               style="height: 48px; border-color: #d1d5db;">
                                        @error('contact_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3" style="color: #1a1a1a;">Operating Hours</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="opening_time" class="form-label fw-medium mb-2">Opening Time</label>
                                        <input type="time" 
                                               class="form-control rounded-3 border @error('opening_time') is-invalid @enderror" 
                                               id="opening_time" 
                                               name="opening_time" 
                                               value="{{ old('opening_time', $unit->opening_time) }}"
                                               style="height: 48px; border-color: #d1d5db;">
                                        @error('opening_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="closing_time" class="form-label fw-medium mb-2">Closing Time</label>
                                        <input type="time" 
                                               class="form-control rounded-3 border @error('closing_time') is-invalid @enderror" 
                                               id="closing_time" 
                                               name="closing_time" 
                                               value="{{ old('closing_time', $unit->closing_time) }}"
                                               style="height: 48px; border-color: #d1d5db;">
                                        @error('closing_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3" style="color: #1a1a1a;">Description</h5>
                            <div class="form-group">
                                <label for="description" class="form-label fw-medium mb-2">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control rounded-3 border @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="5"
                                          placeholder="Enter detailed description of the unit..."
                                          style="border-color: #d1d5db;">{{ old('description', $unit->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border shadow-sm rounded-4 mb-4" style="border-color: #e5e7eb !important;">
                            <div class="card-body p-4">
                                <h5 class="fw-semibold mb-3 d-flex align-items-center gap-2" style="color: #1a1a1a;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                    Unit Photo
                                </h5>
                                
                                @if($unit->photo)
                                    <div class="mb-3">
                                        <div class="rounded-3 overflow-hidden border mb-3" style="height: 200px; background-color: #f9fafb; border-color: #e5e7eb !important;">
                                            <img src="{{ asset('storage/' . $unit->photo) }}" 
                                                 alt="{{ $unit->name }}" 
                                                 class="w-100 h-100 object-fit-cover">
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo" value="1">
                                            <label class="form-check-label text-muted" for="remove_photo">
                                                Remove current photo
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label for="photo" class="form-label fw-medium mb-2">Upload New Photo</label>
                                    <input type="file" 
                                           class="form-control rounded-3 border @error('photo') is-invalid @enderror" 
                                           id="photo" 
                                           name="photo"
                                           style="border-color: #d1d5db;"
                                           accept="image/*">
                                    <small class="text-muted">Max file size: 5MB. Allowed: JPG, PNG, JPEG</small>
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card border shadow-sm rounded-4 mb-4" style="border-color: #e5e7eb !important;">
                            <div class="card-body p-4">
                                <h5 class="fw-semibold mb-3 d-flex align-items-center gap-2" style="color: #1a1a1a;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M12 16v-4M12 8h.01" />
                                    </svg>
                                    Settings
                                </h5>
                                
                                <div class="space-y-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', $unit->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="is_active">
                                            Active Unit
                                        </label>
                                        <small class="text-muted d-block">Unit will be visible in the system</small>
                                    </div>

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="featured" 
                                               name="featured" 
                                               value="1"
                                               {{ old('featured', $unit->featured) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="featured">
                                            Featured Unit
                                        </label>
                                        <small class="text-muted d-block">Unit will be highlighted as featured</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border shadow-sm rounded-4" style="border-color: #e5e7eb !important;">
                            <div class="card-body p-4">
                                <h5 class="fw-semibold mb-3 d-flex align-items-center gap-2" style="color: #1a1a1a;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" 
                                         fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    Unit Information
                                </h5>
                                
                                <div class="space-y-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Created</span>
                                        <span class="fw-medium">{{ $unit->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Last Updated</span>
                                        <span class="fw-medium">{{ $unit->updated_at->format('M d, Y') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Database ID</span>
                                        <span class="fw-medium">#{{ str_pad($unit->id, 6, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top mt-4 pt-4 d-flex justify-content-end gap-3">
                    <a href="{{ route('admin.units.view') }}" 
                       class="btn btn-outline-secondary rounded-pill px-4 d-flex align-items-center gap-2"
                       style="height: 48px; border-color: #d1d5db;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="fw-medium">Cancel</span>
                    </a>
                    <button type="submit" 
                            class="btn rounded-pill px-4 d-flex align-items-center gap-2"
                            style="height: 48px; background-color: #f59e0b; border-color: #f59e0b; color: white;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        <span class="fw-medium">Update Unit</span>
                    </button>
                </div>
            </form>
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

    .btn {
        transition: all 0.15s ease;
    }

    .btn:hover {
        transform: none;
    }

    .btn-outline-secondary:hover {
        background-color: #f9fafb;
        border-color: #6b7280;
        color: #374151;
    }

    .form-control, .form-select {
        transition: all 0.2s ease;
        border: 1px solid #d1d5db !important;
    }

    .form-control:focus, .form-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        outline: none;
    }

    .form-check-input:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .form-check-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    }

    .form-switch .form-check-input:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .is-invalid {
        border-color: #dc2626 !important;
    }

    .is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
    }

    .invalid-feedback {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .alert-danger {
        background-color: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    input[type="file"]::file-selector-button {
        border: none;
        background: #f3f4f6;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        margin-right: 1rem;
        transition: background-color 0.2s;
    }

    input[type="file"]::file-selector-button:hover {
        background: #e5e7eb;
    }

    .space-y-3 > * + * {
        margin-top: 0.75rem;
    }

    .space-y-2 > * + * {
        margin-top: 0.5rem;
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

        .d-flex.justify-content-end.gap-3 {
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editUnitForm');
        const openingTime = document.getElementById('opening_time');
        const closingTime = document.getElementById('closing_time');
        const description = document.getElementById('description');
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            const requiredFields = [
                { id: 'name', minLength: 1 },
                { id: 'officer_name', minLength: 1 },
                { id: 'type', minLength: 1 },
                { id: 'location', minLength: 1 },
                { id: 'description', minLength: 10 }
            ];
            
            requiredFields.forEach(field => {
                const element = document.getElementById(field.id);
                if (!element.value.trim() || element.value.trim().length < field.minLength) {
                    element.classList.add('is-invalid');
                    isValid = false;
                } else {
                    element.classList.remove('is-invalid');
                }
            });
            
            if (openingTime.value && closingTime.value && openingTime.value >= closingTime.value) {
                closingTime.classList.add('is-invalid');
                isValid = false;
            } else {
                closingTime.classList.remove('is-invalid');
            }
            
            if (description.value.trim().length < 10) {
                description.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    firstError.focus();
                }
            }
        });
        
        if (description) {
            description.addEventListener('input', function() {
                if (this.value.trim().length >= 10) {
                    this.classList.remove('is-invalid');
                }
            });
        }
        
        const textFields = form.querySelectorAll('input[type="text"], input[type="email"]');
        textFields.forEach(field => {
            field.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
        
        if (openingTime && closingTime) {
            openingTime.addEventListener('change', validateTimes);
            closingTime.addEventListener('change', validateTimes);
        }
        
        function validateTimes() {
            if (openingTime.value && closingTime.value && openingTime.value >= closingTime.value) {
                closingTime.classList.add('is-invalid');
            } else {
                closingTime.classList.remove('is-invalid');
            }
        }
        
        const removePhotoCheckbox = document.getElementById('remove_photo');
        const photoInput = document.getElementById('photo');
        
        if (removePhotoCheckbox && photoInput) {
            removePhotoCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    photoInput.disabled = true;
                } else {
                    photoInput.disabled = false;
                }
            });
        }
    });
</script>
@endsection
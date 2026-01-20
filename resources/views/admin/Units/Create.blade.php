@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header dengan Judul dan Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold mb-0" style="color: #1a1a1a;">Create New Unit</h1>
            <p class="text-muted mb-0">Add a new inventory unit to the system</p>
        </div>
        <a href="{{ route('admin.units.view') }}" class="btn btn-outline-secondary rounded-pill px-4 d-flex align-items-center gap-2 shadow-sm"
            style="height: 44px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            <span class="fw-medium">Back to Units</span>
        </a>
    </div>

    <!-- Alerts -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center" role="alert">
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

    <!-- Main Form Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.units.store') }}" method="POST" enctype="multipart/form-data" id="unitForm">
                @csrf

                <!-- Basic Information Section -->
                <div class="mb-5">
                    <h5 class="fw-semibold mb-3" style="color: #1a1a1a;">Basic Information</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label fw-medium mb-2">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control rounded-3 border-1 @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       placeholder="Enter unit name"
                                       style="height: 48px; border-color: #d1d5db;"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="officer_name" class="form-label fw-medium mb-2">Officer Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control rounded-3 border-1 @error('officer_name') is-invalid @enderror" 
                                       id="officer_name" 
                                       name="officer_name" 
                                       value="{{ old('officer_name') }}"
                                       placeholder="Enter officer's name"
                                       style="height: 48px; border-color: #d1d5db;"
                                       required>
                                @error('officer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type" class="form-label fw-medium mb-2">Unit Type <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control rounded-3 border-1 @error('type') is-invalid @enderror" 
                                       id="type" 
                                       name="type" 
                                       value="{{ old('type') }}"
                                       placeholder="e.g., Storage, Warehouse, Office"
                                       style="height: 48px; border-color: #d1d5db;"
                                       required>
                                <small class="text-muted">Enter the type of unit</small>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location" class="form-label fw-medium mb-2">Location <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control rounded-3 border-1 @error('location') is-invalid @enderror" 
                                       id="location" 
                                       name="location" 
                                       value="{{ old('location') }}"
                                       placeholder="Enter unit location"
                                       style="height: 48px; border-color: #d1d5db;"
                                       required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Contact Information -->
                <div class="mb-5">
                    <h5 class="fw-semibold mb-3" style="color: #1a1a1a;">Status & Contact</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label fw-medium mb-2">Status <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3 border-1 @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status"
                                        style="height: 48px; border-color: #d1d5db;"
                                        required>
                                    <option value="">Select Status</option>
                                    <option value="OPEN" {{ old('status') == 'OPEN' ? 'selected' : '' }}>Open</option>
                                    <option value="CLOSED" {{ old('status') == 'CLOSED' ? 'selected' : '' }}>Closed</option>
                                    <option value="FULL" {{ old('status') == 'FULL' ? 'selected' : '' }}>Full</option>
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
                                       class="form-control rounded-3 border-1 @error('contact_email') is-invalid @enderror" 
                                       id="contact_email" 
                                       name="contact_email" 
                                       value="{{ old('contact_email') }}"
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
                                       class="form-control rounded-3 border-1 @error('contact_phone') is-invalid @enderror" 
                                       id="contact_phone" 
                                       name="contact_phone" 
                                       value="{{ old('contact_phone') }}"
                                       placeholder="Enter contact phone"
                                       style="height: 48px; border-color: #d1d5db;">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="photo" class="form-label fw-medium mb-2">Unit Photo</label>
                                <input type="file" 
                                       class="form-control rounded-3 border-1 @error('photo') is-invalid @enderror" 
                                       id="photo" 
                                       name="photo"
                                       style="height: 48px; border-color: #d1d5db;"
                                       accept="image/*">
                                <small class="text-muted">Max file size: 5MB. Allowed: JPG, PNG, JPEG</small>
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="mb-5">
                    <h5 class="fw-semibold mb-3" style="color: #1a1a1a;">Operating Hours</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="opening_time" class="form-label fw-medium mb-2">Opening Time</label>
                                <input type="time" 
                                       class="form-control rounded-3 border-1 @error('opening_time') is-invalid @enderror" 
                                       id="opening_time" 
                                       name="opening_time" 
                                       value="{{ old('opening_time') }}"
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
                                       class="form-control rounded-3 border-1 @error('closing_time') is-invalid @enderror" 
                                       id="closing_time" 
                                       name="closing_time" 
                                       value="{{ old('closing_time') }}"
                                       style="height: 48px; border-color: #d1d5db;">
                                @error('closing_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-5">
                    <h5 class="fw-semibold mb-3" style="color: #1a1a1a;">Description</h5>
                    <div class="form-group">
                        <label for="description" class="form-label fw-medium mb-2">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3 border-1 @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="5"
                                  placeholder="Enter detailed description of the unit..."
                                  style="border-color: #d1d5db;"
                                  required>{{ old('description') }}</textarea>
                        <small class="text-muted">Minimum 10 characters required</small>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Additional Settings -->
                <div class="mb-4">
                    <h5 class="fw-semibold mb-3" style="color: #1a1a1a;">Additional Settings</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="is_active">
                                    Active Unit
                                </label>
                                <small class="text-muted d-block">Unit will be visible in the system</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="featured" 
                                       name="featured" 
                                       value="1"
                                       {{ old('featured') ? 'checked' : '' }}>
                                <label class="form-check-label fw-medium" for="featured">
                                    Featured Unit
                                </label>
                                <small class="text-muted d-block">Unit will be highlighted as featured</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                    <a href="{{ route('admin.units.view') }}" 
                       class="btn btn-outline-secondary rounded-pill px-4 d-flex align-items-center gap-2"
                       style="height: 48px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="fw-medium">Cancel</span>
                    </a>
                    <button type="submit" 
                            class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2 shadow-sm"
                            style="height: 48px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="white" stroke-width="2.5">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        <span class="fw-medium">Create Unit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Custom Styles for Create Page */
    .form-control, .form-select {
        transition: all 0.2s ease;
        border: 1px solid #d1d5db !important;
    }

    .form-control:focus, .form-select:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        outline: none;
    }

    .form-label {
        color: #374151;
        font-size: 0.875rem;
    }

    .form-check-input:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .form-check-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    }

    .btn-outline-secondary:hover {
        background-color: #f9fafb;
        border-color: #9ca3af;
    }

    .btn-primary {
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
    }

    /* Section styling */
    h5 {
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0.75rem;
    }

    /* Textarea styling */
    textarea.form-control {
        resize: vertical;
        min-height: 120px;
    }

    /* File input styling */
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

    /* Invalid state styling */
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
</style>

<script>
    // Form validation and time validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('unitForm');
        const openingTime = document.getElementById('opening_time');
        const closingTime = document.getElementById('closing_time');
        
        form.addEventListener('submit', function(e) {
            // Basic validation
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Time validation
            if (openingTime.value && closingTime.value) {
                if (openingTime.value >= closingTime.value) {
                    closingTime.classList.add('is-invalid');
                    closingTime.nextElementSibling.innerHTML = 'Closing time must be after opening time';
                    isValid = false;
                } else {
                    closingTime.classList.remove('is-invalid');
                }
            }
            
            // Description length validation
            const description = document.getElementById('description');
            if (description.value.trim().length < 10) {
                description.classList.add('is-invalid');
                description.nextElementSibling.innerHTML = 'Description must be at least 10 characters long';
                isValid = false;
            } else {
                description.classList.remove('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                
                // Scroll to first error
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
        
        // Real-time validation for description
        const description = document.getElementById('description');
        if (description) {
            description.addEventListener('input', function() {
                if (this.value.trim().length >= 10) {
                    this.classList.remove('is-invalid');
                }
            });
        }
        
        // Real-time validation for required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
        
        // Time validation on change
        if (openingTime && closingTime) {
            openingTime.addEventListener('change', validateTimes);
            closingTime.addEventListener('change', validateTimes);
        }
        
        function validateTimes() {
            if (openingTime.value && closingTime.value && openingTime.value >= closingTime.value) {
                closingTime.classList.add('is-invalid');
                closingTime.nextElementSibling.innerHTML = 'Closing time must be after opening time';
            } else {
                closingTime.classList.remove('is-invalid');
            }
        }
    });
</script>
@endsection
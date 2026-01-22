@if($units->count())
@foreach($units as $unit)
@php
    $capacity = $unit->capacity ?? 0;
    $progressColor = $capacity >= 90 ? 'bg-danger' : ($capacity >= 70 ? 'bg-warning' : 'bg-primary');

    $statusMap = [
        'OPEN' => ['label'=>'Open','class'=>'bg-success-subtle text-success'],
        'CLOSED' => ['label'=>'Closed','class'=>'bg-danger-subtle text-danger'],
        'FULL' => ['label'=>'Full','class'=>'bg-warning-subtle text-warning']
    ];
    $status = $statusMap[$unit->status] ?? $statusMap['OPEN'];
@endphp

<tr>
    <td class="ps-4">
        <div class="fw-semibold">{{ $unit->name }}</div>
        <div class="text-muted" style="font-size:.75rem">
            {{ $unit->officer_name ?? 'No officer' }}
        </div>
    </td>

    <td>
        <span class="badge rounded-pill bg-light text-dark border px-3">
            {{ $unit->type ?? 'N/A' }}
        </span>
    </td>

    <td>
        <span class="badge rounded-pill px-3 {{ $status['class'] }}">
            {{ $status['label'] }}
        </span>
    </td>

    <td style="min-width:160px">
        <div class="progress" style="height:6px">
            <div class="progress-bar {{ $progressColor }}"
                 style="width:{{ $capacity }}%"></div>
        </div>
        <small class="text-muted">{{ $capacity }}%</small>
    </td>

   <td class="text-center pe-4">
    <div class="d-flex justify-content-center gap-1">
        <!-- View Button -->
        <a href="{{ route('admin.units.show', $unit->id) }}"
           class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
           style="width: 32px; height: 32px; border-color: #d1d5db;"
           data-bs-toggle="tooltip"
           data-bs-title="View Details"
           onmouseover="this.style.backgroundColor='#eff6ff'; this.style.borderColor='#3b82f6'; this.querySelector('svg').style.stroke='#3b82f6';"
           onmouseout="this.style.backgroundColor='transparent'; this.style.borderColor='#d1d5db'; this.querySelector('svg').style.stroke='#6b7280';">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                 fill="none" stroke="#6b7280" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </a>

        <!-- Edit Button -->
        <a href="{{ route('admin.units.edit', $unit->id) }}"
           class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
           style="width: 32px; height: 32px; border-color: #d1d5db;"
           data-bs-toggle="tooltip"
           data-bs-title="Edit Unit"
           onmouseover="this.style.backgroundColor='#f0fdf4'; this.style.borderColor='#16a34a'; this.querySelector('svg').style.stroke='#16a34a';"
           onmouseout="this.style.backgroundColor='transparent'; this.style.borderColor='#d1d5db'; this.querySelector('svg').style.stroke='#6b7280';">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                 fill="none" stroke="#6b7280" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </a>

        <!-- Delete Button -->
        <button type="button"
           class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center"
           style="width: 32px; height: 32px; border-color: #d1d5db;"
           data-bs-toggle="modal"
           data-bs-target="#deleteModal{{ $unit->id }}"
           data-bs-title="Delete Unit"
           onmouseover="this.style.backgroundColor='#fef2f2'; this.style.borderColor='#dc2626'; this.querySelector('svg').style.stroke='#dc2626';"
           onmouseout="this.style.backgroundColor='transparent'; this.style.borderColor='#d1d5db'; this.querySelector('svg').style.stroke='#6b7280';">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                 fill="none" stroke="#6b7280" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </button>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal{{ $unit->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-red-100 p-2 rounded-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" 
                                 fill="none" stroke="#dc2626" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                        <h5 class="modal-title fw-semibold" style="color: #1a1a1a;">Confirm Delete</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <p class="mb-0">Are you sure you want to delete <strong>{{ $unit->name }}</strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('admin.units.destroy', $unit->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4">
                            Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</td>
</tr>
@endforeach
@else
<tr>
    <td colspan="6" class="text-center py-5 text-muted">
        No units found
    </td>
</tr>
@endif

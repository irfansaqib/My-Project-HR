@php
    $query = \App\Models\Announcement::active();

    // Filter for Client Portal
    if (request()->is('portal*')) {
        $query->where('is_client_visible', true);
    }

    $activeAnnouncements = $query->get();
@endphp

@if($activeAnnouncements->isNotEmpty())
    <div class="mb-4">
        @foreach($activeAnnouncements as $announcement)
            {{-- 
               DESIGN: 
               - bg-white: Fixed White Background
               - border-start border-4: Colored strip on left
               - border-{type}: Sets the strip color
            --}}
            <div class="alert bg-white shadow-sm border-0 border-start border-4 border-{{ $announcement->type }} d-flex p-3 position-relative" role="alert">
                
                {{-- 1. ICON SECTION (Colored by Type) --}}
                <div class="me-3">
                    <div class="mt-1">
                        @if($announcement->type == 'danger') 
                            <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                        @elseif($announcement->type == 'warning') 
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        @elseif($announcement->type == 'success') 
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        @else 
                            <i class="fas fa-info-circle fa-2x text-info"></i>
                        @endif
                    </div>
                </div>

                {{-- 2. CONTENT SECTION --}}
                <div class="flex-grow-1">
                    {{-- Title: Bold and Colored based on Type --}}
                    <h5 class="fw-bold mb-1 text-{{ $announcement->type }}" style="font-size: 1.1rem;">
                        {{ $announcement->title }}
                    </h5>
                    
                    {{-- Message: Always Dark Grey for readability --}}
                    <div class="text-secondary text-dark" style="font-size: 0.95rem; line-height: 1.5; opacity: 0.8;">
                        {!! nl2br(e($announcement->message)) !!}
                    </div>
                </div>

                {{-- 3. CLOSE BUTTON --}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    </div>
@endif
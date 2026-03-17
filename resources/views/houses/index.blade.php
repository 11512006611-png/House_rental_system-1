@extends('layouts.app')

@section('title', 'Explore Houses')

@section('content')

<!-- Search Banner -->
<section style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #2d5a8e 100%); padding: 48px 0 56px;">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="text-white fw-bold mb-1" style="font-size:2rem;">Find Your Perfect Home</h2>
            <p class="text-white-50 mb-0">Search from {{ $houses->total() }} available houses across all Dzongkhags</p>
        </div>

        <!-- Search Bar Card -->
        <div class="mx-auto" style="max-width:900px;">
            <div class="bg-white rounded-4 shadow-lg p-4">
                <form action="{{ route('houses.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                <i class="fas fa-map-marker-alt text-hrs-primary me-1"></i> Dzongkhag
                            </label>
                            <select name="location" class="form-select form-select-lg">
                                <option value="">All Locations</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ request('location') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->dzongkhag_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                <i class="fas fa-home text-hrs-primary me-1"></i> House Type
                            </label>
                            <select name="type" class="form-select form-select-lg">
                                <option value="">All Types</option>
                                @foreach($houseTypes as $type)
                                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                <i class="fas fa-search text-hrs-primary me-1"></i> Keyword
                            </label>
                            <input type="text" name="search" class="form-control form-control-lg"
                                   placeholder="Search..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-hrs-primary btn-lg w-100">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </div>

                    <!-- Extra filters row -->
                    <div class="row g-3 mt-1 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                <i class="fas fa-tag text-hrs-primary me-1"></i> Min Price (Nu.)
                            </label>
                            <input type="number" name="min_price" class="form-control"
                                   placeholder="Min" value="{{ request('min_price') }}" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-dark mb-1">
                                <i class="fas fa-tag text-hrs-primary me-1"></i> Max Price (Nu.)
                            </label>
                            <input type="number" name="max_price" class="form-control"
                                   placeholder="Max" value="{{ request('max_price') }}" min="0">
                        </div>
                        <div class="col-md-4"></div>
                        <div class="col-md-2">
                            @if(request()->hasAny(['location','type','search','min_price','max_price']))
                            <a href="{{ route('houses.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i> Clear
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Houses Listing -->
<div class="container py-5">

    <!-- Results Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="fw-semibold fs-5">{{ $houses->total() }}</span>
            <span class="text-muted"> houses found</span>
            @if(request()->hasAny(['location','type','search','min_price','max_price']))
                <span class="badge ms-2" style="background:#8b0000;">Filtered</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-3">
            @auth
            @if(Auth::user()->isOwner() || Auth::user()->isAdmin())
            <a href="{{ route('houses.create') }}" class="btn btn-hrs-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Post a House
            </a>
            @endif
            @endauth
            <span class="text-muted small">Page {{ $houses->currentPage() }} of {{ $houses->lastPage() }}</span>
        </div>
    </div>

    @if($houses->count() > 0)
    <div class="row g-4" id="housesGrid">
        @foreach($houses as $house)
        @if($loop->iteration === 4)<div id="hiddenHouses" class="col-12 p-0"><div class="row g-4 m-0">@endif
        <div class="col-lg-4 col-md-6 {{ $loop->iteration > 3 ? 'hidden-house-col' : '' }}">
            <div class="house-card h-100">
                <div class="house-card-img-wrapper">
                    <img src="{{ $house->image_url }}"
                         alt="{{ $house->title }}"
                         class="house-card-img">
                    <div class="house-card-badges">
                        <span class="badge-status badge-available">Available</span>
                        <span class="badge-type">{{ $house->type }}</span>
                    </div>
                    <div class="house-card-price">
                        <span>Nu. {{ number_format($house->price, 0) }}</span>
                        <small>/month</small>
                    </div>
                </div>
                <div class="house-card-body">
                    <h5 class="house-card-title">{{ $house->title }}</h5>
                    <p class="house-card-location">
                        <i class="fas fa-map-marker-alt text-hrs-primary me-1"></i>
                        {{ $house->location }}
                        @if($house->locationModel)
                            — {{ $house->locationModel->dzongkhag_name }}
                        @endif
                    </p>
                    <div class="house-card-features">
                        <span><i class="fas fa-bed"></i> {{ $house->bedrooms }} Bed</span>
                        <span><i class="fas fa-bath"></i> {{ $house->bathrooms }} Bath</span>
                        @if($house->area)
                        <span><i class="fas fa-ruler-combined"></i> {{ $house->area }}</span>
                        @endif
                    </div>

                    @if($house->description)
                    <div class="house-desc-wrap mt-2" data-id="{{ $house->id }}">
                        <p class="house-desc-short small text-muted mb-1">
                            {{ Str::limit($house->description, 90) }}
                        </p>
                        <p class="house-desc-full small text-muted mb-1" style="display:none;">
                            {{ $house->description }}
                        </p>
                        @if(strlen($house->description) > 90)
                        <button type="button" class="btn-see-toggle"
                                onclick="toggleDesc(this, {{ $house->id }})">
                            <i class="fas fa-chevron-down me-1"></i> See More
                        </button>
                        @endif
                    </div>
                    @endif

                    <a href="{{ route('houses.show', $house) }}" class="btn btn-hrs-outline w-100 mt-3">
                        Explore More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @if($loop->last && $loop->iteration > 3)</div></div>@endif
        @endforeach
    </div>

    <!-- See More / See Less button -->
    @if($houses->count() > 3)
    <div class="text-center mt-4" id="seeMoreWrapper">
        <button id="seeMoreBtn" class="btn btn-see-more-list" onclick="toggleHouseList()">
            <i class="fas fa-chevron-down me-2" id="seeMoreIcon"></i>
            <span id="seeMoreText">See More ({{ $houses->count() - 3 }} more)</span>
        </button>
    </div>
    @endif

    <!-- Pagination -->
    <div class="mt-5 d-flex justify-content-center">
        {{ $houses->withQueryString()->links('pagination::bootstrap-5') }}
    </div>

    @else
    <div class="empty-state text-center py-5">
        <i class="fas fa-home fa-4x text-muted mb-3 d-block"></i>
        <h5 class="text-muted">No houses found</h5>
        <p class="text-muted small">Try adjusting your filters or search terms.</p>
        <a href="{{ route('houses.index') }}" class="btn btn-hrs-primary">Clear Filters</a>
    </div>
    @endif

</div>

@endsection

@push('styles')
<style>
.btn-see-toggle {
    background: none;
    border: none;
    padding: 0;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--hrs-primary, #8b0000);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    transition: color 0.2s;
}
.btn-see-toggle:hover { color: var(--hrs-secondary, #fbbf24); }
.btn-see-toggle i { transition: transform 0.25s; }
.btn-see-toggle.expanded i { transform: rotate(180deg); }

/* See More / See Less list button */
#hiddenHouses {
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    transition: max-height 0.55s cubic-bezier(0.4,0,0.2,1), opacity 0.4s ease;
    width: 100%;
}
#hiddenHouses.expanded {
    max-height: 4000px;
    opacity: 1;
}
.btn-see-more-list {
    background: #fff;
    border: 2px solid #8b0000;
    color: #8b0000;
    font-weight: 700;
    font-size: .92rem;
    padding: .55rem 2.2rem;
    border-radius: 50px;
    transition: background .2s, color .2s, transform .15s;
    box-shadow: 0 2px 10px rgba(139,0,0,.10);
}
.btn-see-more-list:hover {
    background: #8b0000;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(139,0,0,.18);
}
.btn-see-more-list #seeMoreIcon { transition: transform .35s; }
.btn-see-more-list.expanded #seeMoreIcon { transform: rotate(180deg); }
</style>
@endpush

@push('scripts')
<script>
function toggleHouseList() {
    const hidden  = document.getElementById('hiddenHouses');
    const btn     = document.getElementById('seeMoreBtn');
    const icon    = document.getElementById('seeMoreIcon');
    const text    = document.getElementById('seeMoreText');
    const count   = document.querySelectorAll('#hiddenHouses .hidden-house-col').length;
    const isOpen  = hidden.classList.contains('expanded');

    if (isOpen) {
        hidden.classList.remove('expanded');
        btn.classList.remove('expanded');
        text.textContent = 'See More (' + count + ' more)';
        // Scroll back up to the grid smoothly
        document.getElementById('housesGrid').scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        hidden.classList.add('expanded');
        btn.classList.add('expanded');
        text.textContent = 'See Less';
    }
}

function toggleDesc(btn, id) {
    const wrap  = btn.closest('.house-desc-wrap');
    const short = wrap.querySelector('.house-desc-short');
    const full  = wrap.querySelector('.house-desc-full');
    const isExpanded = btn.classList.contains('expanded');

    if (isExpanded) {
        full.style.display  = 'none';
        short.style.display = '';
        btn.classList.remove('expanded');
        btn.innerHTML = '<i class="fas fa-chevron-down me-1"></i> See More';
    } else {
        short.style.display = 'none';
        full.style.display  = '';
        btn.classList.add('expanded');
        btn.innerHTML = '<i class="fas fa-chevron-up me-1"></i> See Less';
    }
}
</script>
@endpush

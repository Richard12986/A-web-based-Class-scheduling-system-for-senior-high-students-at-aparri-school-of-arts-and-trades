<div class="topbar d-flex justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="mobile-menu-btn" id="openSidebarBtn">
            <i class="bi bi-list"></i>
        </button>

        <div>
            <div class="page-title">{{ $pageTitle ?? 'Class Scheduling System' }}</div>
            <div class="page-subtitle">{{ $pageSubtitle ?? 'Laravel 11 Prototype Workspace' }}</div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">

        <span class="badge rounded-pill text-bg-light border px-3 py-2">
            {{ \Illuminate\Support\Facades\Auth::user()?->name ?? 'Administrator' }}
        </span>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm px-3 py-2 rounded-pill">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
    </div>
</div>
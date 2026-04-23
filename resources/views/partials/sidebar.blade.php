@php
    $currentRoute = request()->route()?->getName();
@endphp

<div class="sidebar" id="appSidebar">
    <div class="sidebar-top-mobile">
        <div class="fw-bold">Menu</div>
        <button type="button" class="sidebar-close-btn" id="closeSidebarBtn">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="sidebar-brand">
        Class Scheduling System
        <div class="sidebar-subtitle">Proposal Prototype Version</div>
    </div>

    <div class="nav-section-label">Main Modules</div>

    <nav class="nav flex-column">
        <a href="{{ route('dashboard.index') }}"
           class="nav-link {{ str_starts_with($currentRoute, 'dashboard.') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('academic-setup.index') }}"
           class="nav-link {{ str_starts_with($currentRoute, 'academic-setup.') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i>
            <span>Academic Setup</span>
        </a>

        <a href="{{ route('teachers.index') }}"
           class="nav-link {{ str_starts_with($currentRoute, 'teachers.') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i>
            <span>Teachers</span>
        </a>

        <a href="{{ route('scheduling.index') }}"
           class="nav-link {{ str_starts_with($currentRoute, 'scheduling.') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i>
            <span>Scheduling</span>
        </a>

        <a href="{{ route('reports.index') }}"
           class="nav-link {{ str_starts_with($currentRoute, 'reports.') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph-fill"></i>
            <span>Reports</span>
        </a>
    </nav>
</div>
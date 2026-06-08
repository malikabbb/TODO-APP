<div class="topbar">
    {{-- Search --}}
    <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text"
               class="search-input"
               wire:model.live.debounce.300ms="search"
               placeholder="Search tasks…">
    </div>

    {{-- User section --}}
    <div style="display:flex;align-items:center;gap:12px;position:relative;" x-data="{ open: false }">
        <button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch theme">
            <svg class="theme-toggle-icon theme-toggle-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="4"/>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
            </svg>
            <svg class="theme-toggle-icon theme-toggle-moon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
        <span style="font-size:13px;color:var(--theme-text-secondary);">{{ Auth::user()->name }}</span>
        <div class="avatar-btn" @click="open=!open" @click.outside="open=false">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
        <div class="user-menu" x-show="open" x-transition style="display:none;">
            <a href="{{ route('dashboard') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <div class="divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>

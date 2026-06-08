@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">⚙️ Settings</h1>
        <p class="page-subtitle">Manage your account preferences.</p>
    </div>
</div>

<div class="settings-grid">
    <div class="settings-panel">
        <h2 class="settings-section-title">Account Information</h2>
        <div class="form-group">
            <label class="form-label">Name</label>
            <input type="text" class="form-input" value="{{ Auth::user()->name }}" readonly>
        </div>
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-input" value="{{ Auth::user()->email }}" readonly>
        </div>
        <p class="settings-note">Profile editing coming in a future update.</p>
    </div>

    <div class="settings-panel">
        <h2 class="settings-section-title">Appearance</h2>
        <div class="settings-appearance-row">
            <p class="settings-appearance-copy">
                Choose light or dark mode. Your preference is saved only in this browser.
            </p>
            <button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch theme">
                <svg class="theme-toggle-icon theme-toggle-sun" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
                </svg>
                <svg class="theme-toggle-icon theme-toggle-moon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>
    </div>
</div>
@endsection

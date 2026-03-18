@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">⚙️ Settings</h1>
        <p class="page-subtitle">Manage your account preferences.</p>
    </div>
</div>

<div style="background:rgba(22,22,42,0.7);border:1px solid rgba(124,58,237,0.12);border-radius:16px;padding:28px;max-width:520px;">
    <h2 style="color:#F1F0FF;font-size:16px;font-weight:700;margin-bottom:20px;">Account Information</h2>
    <div class="form-group">
        <label class="form-label">Name</label>
        <input type="text" class="form-input" value="{{ Auth::user()->name }}" readonly>
    </div>
    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" class="form-input" value="{{ Auth::user()->email }}" readonly>
    </div>
    <p style="color:#6B6489;font-size:13px;margin-top:16px;">Profile editing coming in a future update.</p>
</div>
@endsection

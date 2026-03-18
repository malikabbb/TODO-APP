@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">👋 Welcome back, {{ Auth::user()->name }}!</h1>
        <p class="page-subtitle">Here's what's on your plate today.</p>
    </div>
</div>

@livewire('task-list')
@endsection

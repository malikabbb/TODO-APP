@extends('layouts.app')
@section('title', 'All Tasks')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">📋 All Tasks</h1>
        <p class="page-subtitle">All your tasks in one place.</p>
    </div>
</div>

@livewire('task-list', ['filter' => 'all'])
@endsection

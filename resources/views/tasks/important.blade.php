@extends('layouts.app')
@section('title', 'Important Tasks')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">⭐ Important Tasks</h1>
        <p class="page-subtitle">Your high-priority items.</p>
    </div>
</div>

@livewire('task-list', ['filter' => 'important'])
@endsection

@extends('layouts.app')
@section('title', 'Completed Tasks')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">✅ Completed Tasks</h1>
        <p class="page-subtitle">All tasks you've finished.</p>
    </div>
</div>

@livewire('task-list', ['filter' => 'completed'])
@endsection

<div>
    {{-- ─── Stat chips ──────────────────────────────── --}}
    <div class="stat-chips">
        <div class="stat-chip">
            <div class="stat-chip-value">{{ $counts['all'] }}</div>
            <div class="stat-chip-label">Total</div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-value">{{ $counts['pending'] }}</div>
            <div class="stat-chip-label">Pending</div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-value">{{ $counts['completed'] }}</div>
            <div class="stat-chip-label">Done</div>
        </div>
        <div class="stat-chip">
            <div class="stat-chip-value">{{ $counts['important'] }}</div>
            <div class="stat-chip-label">Important</div>
        </div>
    </div>

    {{-- ─── Filter tabs ─────────────────────────────── --}}
    <div class="filter-tabs">
        <button class="filter-tab {{ $filter === 'all' ? 'active' : '' }}"
                wire:click="$set('filter','all')">All Tasks</button>
        <button class="filter-tab {{ $filter === 'completed' ? 'active' : '' }}"
                wire:click="$set('filter','completed')">Completed</button>
        <button class="filter-tab {{ $filter === 'important' ? 'active' : '' }}"
                wire:click="$set('filter','important')">Important</button>
    </div>

    {{-- ─── Task list ───────────────────────────────── --}}
    <div id="task-list" wire:ignore.self>
        @forelse($tasks as $task)
            <div class="task-card priority-{{ $task->priority }} {{ $task->isCompleted() ? 'completed' : '' }}"
                 data-id="{{ $task->id }}"
                 id="task-{{ $task->id }}">

                {{-- Drag handle --}}
                <div style="color:#6B6489;cursor:grab;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="9" cy="6" r="2"/><circle cx="15" cy="6" r="2"/>
                        <circle cx="9" cy="12" r="2"/><circle cx="15" cy="12" r="2"/>
                        <circle cx="9" cy="18" r="2"/><circle cx="15" cy="18" r="2"/>
                    </svg>
                </div>

                {{-- Checkbox --}}
                <button class="task-check {{ $task->isCompleted() ? 'checked' : '' }}"
                        wire:click="toggleComplete({{ $task->id }})"
                        title="{{ $task->isCompleted() ? 'Mark pending' : 'Mark complete' }}">
                    @if($task->isCompleted())
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    @endif
                </button>

                {{-- Content --}}
                <div class="task-content">
                    <div class="task-title">{{ $task->title }}</div>
                    <div class="task-meta">
                        @if($task->due_date)
                            <span class="task-date {{ $task->isOverdue() ? 'date-overdue' : '' }}">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                {{ $task->isOverdue() ? '⚠ ' : '' }}{{ $task->formattedDueDate() }}
                            </span>
                        @endif
                        <span class="priority-badge {{ $task->priority }}">{{ $task->priority }}</span>
                        @if($task->description)
                            <span style="font-size:11px;color:#6B6489">· {{ Str::limit($task->description, 40) }}</span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="task-actions">
                    <button class="icon-btn"
                            wire:click="$dispatch('openEditModal', { taskId: {{ $task->id }} })"
                            title="Edit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                    <button class="icon-btn danger"
                            wire:click="deleteTask({{ $task->id }})"
                            wire:confirm="Delete this task?"
                            title="Delete">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14H6L5 6"/>
                            <path d="M10 11v6M14 11v6"/>
                            <path d="M9 6V4h6v2"/>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <div class="empty-state-title">No tasks here</div>
                <div class="empty-state-text">Click the + button to create your first task</div>
            </div>
        @endforelse
    </div>

    {{-- ─── FAB ─────────────────────────────────────── --}}
    <button class="fab" wire:click="$dispatch('openCreateModal')" title="New Task">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
    </button>
{{-- ─── SortableJS ──────────────────────────────────── --}}
<script>
document.addEventListener('livewire:initialized', function () {
    initSortable();
    Livewire.hook('morph.updated', () => setTimeout(initSortable, 50));
});

function initSortable() {
    const el = document.getElementById('task-list');
    if (!el || el._sortable) return;

    el._sortable = new Sortable(el, {
        animation: 200,
        ghostClass: 'sortable-ghost',
        handle: '[data-id]',
        onEnd: function () {
            const order = [...el.querySelectorAll('[data-id]')].map((el, i) => ({
                id: parseInt(el.dataset.id),
                order: i
            }));
            Livewire.dispatch('tasksReordered', { order });
        }
    });
}
</script>
</div>
